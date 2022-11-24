<?php
namespace Kkigomi\Plugin\PHPDebugBar;

use DebugBar\JavascriptRenderer;
use DebugBar\StandardDebugBar;
use DebugBar\DebugBar;
use DebugBar\DataCollector\MessagesCollector;

class PHPDebugBar
{
    private $queryCollector;
    private $debugbar;
    private $debugbarRenderer;
    private static $instance;
    private static $config = [];

    private function __construct()
    {
        $this->debugbar = new StandardDebugBar();
        $this->debugbarRenderer = $this->debugbar->getJavascriptRenderer();
        $this->debugbarRenderer->setOptions([
            'base_url' => self::$config['pluginUrl'] . '/assets',
        ]);

        $this->debugbarRenderer->addAssets(
            [
                'widgets/sqlqueries/widget.css'
            ],
            [
                'widgets/sqlqueries/widget.js'
            ]
        );

        $this->queryCollector = new QueryCollector();
        $this->debugbar->addCollector($this->queryCollector);

        if (!!stripos(implode('.', [$_SERVER['HTTP_ACCEPT'], $_SERVER['HTTP_CONTENT_TYPE'], $_SERVER['CONTENT_TYPE']]), 'json')) {
            $this->debugbar["messages"]->addMessage("hello world!");
            $this->debugbar->sendDataInHeaders();
        }
    }

    public function render(): void
    {
        echo $this->debugbarRenderer->renderHead();
        echo $this->debugbarRenderer->render();
    }

    public static function getInstance(): self
    {
        return self::$instance ?: self::$instance = new self();
    }

    public function getQueryCollector(): QueryCollector
    {
        return $this->queryCollector;
    }

    public static function setConfig($config)
    {
        self::$config = $config;
    }


    /**
     * `sql_query_after` Hook에서 실행된 DB 쿼리 목록 수집
     * 단, 그누보드 특성상 실행 시점에 따라 처음과 끝의 일부가 수집되지 않을 수 있음
     *
     * @global array @g5;
     */
    public function hookSqlQueryAfter($result = null, $sql = null, $start_time = 0, $end_time = 0): void
    {
        global $g5;

        $duration = $end_time - $start_time;
        $error = [
            'error_code' => mysqli_errno($g5['connect_db']),
            'error_message' => mysqli_error($g5['connect_db']),
        ];
        $rowCount = (!$error['error_code'] && is_object($result)) ? sql_num_rows($result) ?? 0 : 0;

        $this->queryCollector->addQuery($sql, $duration, $rowCount, $error);
    }
}
