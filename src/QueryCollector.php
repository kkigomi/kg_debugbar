<?php
namespace Kkigomi\Plugin\PHPDebugBar;

use DebugBar\DataCollector;

class QueryCollector extends DataCollector\DataCollector implements DataCollector\Renderable
{
    protected $timeCollector;
    protected $queries = [];
    protected $accumulated_duration = 0;

    public function addQuery(string $query, float $duration = 0, int $rowCount = 0, array $error)
    {
        $this->accumulated_duration += $duration;

        $this->queries[] = [
            'sql' => $this->findSource() . $query,
            'duration' => $duration,
            'row_count' => $rowCount,
            'is_success' => !(!!$error['error_code']),
            'error_code' => $error['error_code'],
            'error_message' => $error['error_message'],
            'duration_str' => $this->getDataFormatter()->formatDuration($duration),
        ];
    }

    public function collect()
    {
        return array(
            'nb_statements' => count($this->queries),
            'accumulated_duration' => $this->accumulated_duration,
            'accumulated_duration_str' => $this->getDataFormatter()->formatDuration($this->accumulated_duration),
            'statements' => $this->queries
        );
    }

    public function getName()
    {
        return 'pdo';
    }

    protected function findSource()
    {
        $stack = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        $source = "";
        $found = false;

        foreach ($stack as $index => $trace) {
            if ($trace['function'] === 'sql_query') {
                $found = true;
            }
            if ($stack[$index + 1]['function'] === 'sql_fetch') {
                continue;
            }

            if ($found) {
                $trace['file'] = str_replace($_SERVER['DOCUMENT_ROOT'], '', $trace['file']);
                $source .= "-- ";
                $source .= "{$trace['file']}:{$trace['line']}";

                $parent = $stack[$index + 1];
                if ($parent['function'] && in_array($trace['function'], ['sql_query', 'sql_fetch']) && !in_array($parent['function'], ['sql_fetch', 'include', 'include_once', 'require', 'require_once'])) {
                    if ($parent['class']) {
                        $source .= "\n-- {$parent['class']}{$parent['type']}{$parent['function']}()";
                    } else {
                        $source .= "\n-- {$parent['function']}()";
                    }
                }
                $source .= "\n";
                break;
            }
        }

        return $source;
    }

    protected function parseTrace($index, array $trace)
    {
    }

    public function getWidgets()
    {
        return array(
            "database" => array(
                "icon" => "database",
                'widget' => 'PhpDebugBar.Widgets.SQLQueriesWidget',
                "map" => "pdo",
                "default" => "[]"
            ),
            "database:badge" => array(
                "map" => "pdo.nb_statements",
                "default" => 0
            )
        );
    }

    public function getAssets()
    {
        return array(
            'css' => 'widgets/sqlqueries/widget.css',
            'js' => 'widgets/sqlqueries/widget.js'
        );
    }
}