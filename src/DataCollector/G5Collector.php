<?php
namespace Kkigomi\Plugin\Debugbar\DataCollector;

use DebugBar\DataCollector;
use DebugBar\DataCollector\AssetProvider;

class G5Collector extends DataCollector\DataCollector implements DataCollector\Renderable, AssetProvider
{
    protected $useHtmlVarDumper = false;

    public function collect(): array
    {
        // $constExclude = [
        //     'G5_MYSQL_DB',
        //     'G5_MYSQL_HOST',
        //     'G5_MYSQL_PASSWORD',
        //     'G5_MYSQL_PASSWORD_LENGTH',
        //     'G5_MYSQL_USER',
        //     'G5_ALPHAUPPER',
        //     'G5_ALPHALOWER',
        //     'G5_ALPHABETIC',
        //     'G5_NUMERIC',
        //     'G5_HANGUL',
        //     'G5_SPACE',
        //     'G5_SPECIAL',
        // ];
        // $constAll = get_defined_constants(true)['user'];

        // $const = [
        //     'g5' => [],
        //     'g5_path' => [],
        //     'other' => []
        // ];

        // foreach ($constAll as $key => $val) {
        //     if (in_array($key, $constExclude)) {
        //         continue;
        //     }

        //     if (strpos($key, 'G5_') === 0) {
        //         if (
        //             substr_compare($key, '_PATH', -5) === 0
        //             || substr_compare($key, '_DIR', -4) === 0
        //             || substr_compare($key, '_URL', -4) === 0
        //         ) {
        //             $const['g5_path'][$key] = $val;
        //         } else if (!in_array($key, $constExclude)) {
        //             $const['g5'][$key] = $val;
        //         }
        //     } else {
        //         $const['other'][$key] = $val;
        //     }
        // }
        // ksort($const['g5_path']);
        // ksort($const['g5']);
        // ksort($const['other']);

        return array(
            'version' => \G5_GNUBOARD_VER,
            // 'const' => $const,
        );
    }

    public function getName(): string
    {
        return 'g5';
    }

    /**
     * Sets a flag indicating whether the Symfony HtmlDumper will be used to dump variables for
     * rich variable rendering.
     *
     * @param bool $value
     * @return $this
     */
    public function useHtmlVarDumper($value = true)
    {
        $this->useHtmlVarDumper = $value;
        return $this;
    }

    /**
     * Indicates whether the Symfony HtmlDumper will be used to dump variables for rich variable
     * rendering.
     *
     * @return mixed
     */
    public function isHtmlVarDumperUsed()
    {
        return $this->useHtmlVarDumper;
    }

    public function getWidgets(): array
    {
        $widget = $this->isHtmlVarDumperUsed()
            ? "PhpDebugBar.Widgets.HtmlVariableListWidget"
            : "PhpDebugBar.Widgets.VariableListWidget";

        return array(
            'g5_version' => array(
                'icon' => 'info-circle',
                'tooltip' => '그누보드 버전',
                'map' => 'g5.version',
                'default' => ''
            ),
            // 'g5' => array(
            //     "icon" => "tags",
            //     'map' => 'g5.const.g5',
            //     'widget' => $widget,
            //     'default' => '{}'
            // ),
        );
    }

    public function getAssets()
    {
        return array(
            // 'css' => 'widgets/g5/widget.css',
            // 'js' => 'widgets/g5/widget.js'
        );
    }

}
