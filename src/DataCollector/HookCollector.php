<?php
namespace Kkigomi\Plugin\Debugbar\DataCollector;

use DebugBar\DataCollector;
use DebugBar\DataCollector\AssetProvider;

class HookCollector extends DataCollector\DataCollector implements DataCollector\Renderable, AssetProvider
{
    protected $event = [];
    protected $eventListener = [];
    protected $replace = [];
    protected $replaceListener = [];

    /**
     * @fixme 중복 코드 제거
     */
    public function collect(): array
    {
        $total = [
            'event' => 0,
            'replace' => 0
        ];

        foreach (\get_hook_datas('event') as $tag => $count) {
            if ($tag === 'count') {
                $total['event'] = $count;
                continue;
            }

            $this->event[$tag] = [
                'tag' => $tag,
                'called' => $count,
                'listener' => []
            ];
        }

        foreach (\get_hook_datas('event', 1) as $tag => $list) {
            ksort($list);

            foreach ($list as $priority => $actions) {
                foreach ($actions as $action) {
                    if (!$this->event[$tag]) {
                        $this->event[$tag] = [
                            'tag' => $tag,
                            'called' => 0,
                            'listener' => []
                        ];
                    }

                    if (is_array($action['function'])) {
                        $class = is_string($action['function'][0]) ? $action['function'][0] : get_class($action['function'][0]);
                        $function = $action['function'][1];
                        $ref = new \ReflectionMethod($class, $function);
                        $actualArgumentCount = $ref->getNumberOfParameters();
                        $methodType = $ref->isStatic() ? '::' : '->';
                    } else {
                        $class = null;
                        $function = $action['function'];
                        $ref = new \ReflectionFunction($action['function']);
                        $actualArgumentCount = $ref->getNumberOfParameters();
                    }

                    $this->event[$tag]['listener'][] = [
                        'tag' => $tag,
                        'class' => $class,
                        'function' => $function,
                        'methodType' => $methodType,
                        'priority' => (int) $priority ?? \G5_HOOK_DEFAULT_PRIORITY,
                        'argumentsCount' => (int) $action['arguments'],
                        'actualArgumentCount' => $actualArgumentCount,
                    ];
                }
            }
        }

        foreach (\get_hook_datas('replace') as $tag => $count) {
            if ($tag === 'count') {
                $total['replace'] = $count;
                continue;
            }

            $this->replace[$tag] = [
                'tag' => $tag,
                'called' => $count,
                'listener' => []
            ];
        }

        foreach (\get_hook_datas('replace', 1) as $tag => $list) {
            ksort($list);

            foreach ($list as $priority => $actions) {
                foreach ($actions as $action) {
                    if (!$this->replace[$tag]) {
                        $this->replace[$tag] = [
                            'tag' => $tag,
                            'called' => 0,
                            'listener' => []
                        ];
                    }

                    if (is_array($action['function'])) {
                        $class = is_string($action['function'][0]) ? $action['function'][0] : get_class($action['function'][0]);
                        $function = $action['function'][1];
                        $methodType = null;
                        $ref = new \ReflectionMethod($class, $function);
                        $actualArgumentCount = $ref->getNumberOfParameters();
                        $getParameters = $ref->getParameters();
                        $methodType = $ref->isStatic() ? '::' : '->';
                    } else {
                        $class = null;
                        $methodType = null;
                        $function = $action['function'];
                        $ref = new \ReflectionFunction($action['function']);
                        $actualArgumentCount = $ref->getNumberOfParameters();
                    }


                    $this->replace[$tag]['listener'][] = [
                        'tag' => $tag,
                        'class' => $class,
                        'function' => $function,
                        'methodType' => $methodType,
                        'priority' => (int) $priority ?? \G5_HOOK_DEFAULT_PRIORITY,
                        'argumentsCount' => (int) $action['arguments'],
                        'actualArgumentCount' => $actualArgumentCount,
                    ];
                }
            }
        }

        return array(
            'total' => $total,
            'event' => $this->event,
            'replace' => $this->replace,
        );
    }

    public function getName(): string
    {
        return 'hook';
    }

    public function getWidgets(): array
    {
        return array(
            'hook' => array(
                'icon' => 'anchor',
                'widget' => 'PhpDebugBar.Widgets.HookWidget',
                'map' => 'hook',
                'default' => '[]'
            )
        );
    }

    public function getAssets()
    {
        return array(
            'css' => 'widgets/hook/widget.css',
            'js' => 'widgets/hook/widget.js'
        );
    }
}
