<?php
namespace Kkigomi\Plugin\Debugbar;

class HookListener
{
    use Traits\SingletonTrait;

    private $debugbarInstance;

    protected function singletonInstanceInit()
    {
        $this->debugbarInstance = DebugBar::getInstance();

        

        /*
         * 그누보드의 쿼리 목록이 수집되고 있지 않으면 Hook을 이용해 수집
         * Hook을 이용한 방식의 한계로 초반 일부 쿼리가 수집되지 않을 수 있음
         */
        if (empty($GLOBALS['g5_debug']['sql'])) {
            add_event('sql_query_after', [$this, 'hookSqlQueryAfter'], \G5_HOOK_DEFAULT_PRIORITY, 6);
        }

        add_event('tail_sub', [$this, 'debugbarRender'], 900);
        add_event('alert', [$this, 'listenerAlertHookAction'], 900, 4);
        add_event('alert_close', [$this, 'listenerAlertHookAction'], 900, 1);
    }

    public function listenerAlertHookAction()
    {
        if ($this->debugbarInstance->rendered()) {
            return;
        }

        if (strpos($_SERVER['SCRIPT_FILENAME'], \KG_DEBUGBAR_PATH) === 0) {
            return;
        }

        // `tail_sub` Hook이 호출되지 않았다면 stack
        $this->debugbarInstance = Debugbar::getInstance();
        $this->debugbarInstance->stackData($GLOBALS['g5_debug']['sql'], false);
    }

    

    public function debugbarRender()
    {
        global $is_admin;

        // debugbar 플러그인 경로의 요청은 제외
        if (strpos($_SERVER['SCRIPT_FILENAME'], \KG_DEBUGBAR_PATH) === 0) {
            return;
        }

        // 최고관리자가 아니거나 허용 IP 목록에 없으면 중지
        if ($is_admin !== 'super' && !in_array($_SERVER['REMOTE_ADDR'], \KG_DEBUGBAR_ENABLE_IP)) {
            return;
        }

        $this->debugbarInstance = Debugbar::getInstance();
        echo $this->debugbarInstance->getRenderCode($GLOBALS['g5_debug']['sql']);
    }

    /**
     * `sql_query_after` Hook에서 실행된 DB 쿼리 목록 수집
     * 단, 그누보드 Hook 특성상 처음과 끝의 일부가 누락 됨
     */
    public function hookSqlQueryAfter($result = null, $sql = null, $start_time = 0, $end_time = 0, $error = null, $source = null): void
    {
        global $g5;

        if (!$this->debugbarInstance->booted()) {
            return;
        }

        if (!$error) {
            $error = [
                'error_code' => mysqli_errno($g5['connect_db']),
                'error_message' => mysqli_error($g5['connect_db']),
            ];
        }
        
        $duration = $end_time - $start_time;
        $rowCount = (!$error['error_code'] && is_object($result)) ? sql_num_rows($result) ?? 0 : 0;

        // FIXME #215 PR이 적용되면 제거
        if (!$source) {
            $source = [];
            $stack = debug_backtrace(\DEBUG_BACKTRACE_IGNORE_ARGS);
            $found = false;

            foreach ($stack as $index => $trace) {
                if ($trace['function'] === 'sql_query') {
                    $found = true;
                }
                if (isset($stack[$index + 1]) && $stack[$index + 1]['function'] === 'sql_fetch') {
                    continue;
                }

                if ($found) {
                    $trace['file'] = str_replace($_SERVER['DOCUMENT_ROOT'], '', $trace['file']);
                    $source['file'] = $trace['file'];
                    $source['line'] = $trace['line'];

                    $parent = (isset($stack[$index + 1])) ? $stack[$index + 1] : [];
                    if (isset($parent['function'])) {
                        if (
                            in_array($trace['function'], ['sql_query', 'sql_fetch'])
                            && (
                                isset($parent['function'])
                                && !in_array($parent['function'], ['sql_fetch', 'include', 'include_once', 'require', 'require_once'])
                            )
                        ) {
                            if (isset($parent['class']) && $parent['class']) {
                                $source['class'] = $parent['class'];
                                $source['type'] = $parent['type'];
                                $source['function'] = $parent['function'];
                            } else {
                                $source['function'] = $parent['function'];
                            }
                        }
                    }
                    break;
                }
            }
        }

        $queryCollector = $this->debugbarInstance->getCollector('dbquery');
        $queryCollector->addQuery($sql, $duration, $rowCount, $error, $source);
    }

}
