<?php
namespace Kkigomi\Plugin\Debugbar\DataCollector;

use DebugBar\DataCollector;
use DebugBar\DataCollector\AssetProvider;

class QueryCollector extends DataCollector\DataCollector implements DataCollector\Renderable, AssetProvider
{
    protected $timeCollector;
    protected $queries = [];
    protected $accumulated_duration = 0;

    public function addQuery(string $query, ?float $duration = null, int $rowCount = 0, array $error = [], $trace = null): void
    {
        $query = preg_replace("/\n[ ]*/", " ", $query);

        $source = '';
        if ($trace) {
            $trace['file'] = str_replace($_SERVER['DOCUMENT_ROOT'], '', $trace['file']);
            $source .= "-- ";
            $source .= "{$trace['file']}:{$trace['line']}";

            if (isset($trace['class']) && $trace['class']) {
                $source .= "\n-- {$trace['class']}{$trace['type']}{$trace['function']}()";
            } else if (isset($trace['function'])) {
                $source .= "\n-- {$trace['function']}()";
            }
            $source .= "\n";
        }

        if ($duration) {
            $this->accumulated_duration += $duration;
        }
        $this->queries[] = [
            'sql' => $source . $query,
            'duration' => $duration,
            'row_count' => $rowCount,
            'is_success' => !(!!$error['error_code']),
            'error_code' => $error['error_code'] ?? null,
            'error_message' => $error['error_message'] ?? null,
            'duration_str' => $duration ? $this->getDataFormatter()->formatDuration($duration) : null,
        ];
    }

    public function collect(): array
    {
        return array(
            'nb_statements' => count($this->queries),
            'accumulated_duration' => $this->accumulated_duration,
            'accumulated_duration_str' => $this->accumulated_duration ? $this->getDataFormatter()->formatDuration($this->accumulated_duration) : null,
            'statements' => $this->queries
        );
    }

    public function getName(): string
    {
        return 'dbquery';
    }

    public function getWidgets(): array
    {
        return array(
            'queries' => array(
                'icon' => 'database',
                'widget' => 'PhpDebugBar.Widgets.SQLQueriesWidget',
                'map' => 'dbquery',
                'default' => '[]'
            ),
            'queries:badge' => array(
                'map' => 'dbquery.nb_statements',
                'default' => 0
            )
        );
    }

    public function getAssets(): array
    {
        return array(
            'css' => 'widgets/sqlqueries/widget.css',
            'js' => 'widgets/sqlqueries/widget.js'
        );
    }
}
