<?php
namespace Kkigomi\Plugin\Debugbar;

use \DebugBar\JavascriptRenderer as BaseJavascriptRenderer;

class JavascriptRenderer extends BaseJavascriptRenderer
{
    public function renderHead()
    {
        list($cssFiles, $jsFiles, $inlineCss, $inlineJs, $inlineHead) = $this->getAssets(null, self::RELATIVE_URL);
        $html = '';

        $nonce = $this->getNonceAttribute();

        foreach ($cssFiles as $url) {
            $mtime = filemtime(str_replace(\KG_DEBUGBAR_URL, \KG_DEBUGBAR_PATH, $url));
            $url .= '?ver=' . $mtime;
            $html .= sprintf('<link rel="stylesheet" type="text/css" href="%s">' . "\n", $url);
        }

        foreach ($inlineCss as $content) {
            $html .= sprintf('<style type="text/css">%s</style>' . "\n", $content);
        }

        foreach ($jsFiles as $url) {
            $mtime = filemtime(str_replace(\KG_DEBUGBAR_URL, \KG_DEBUGBAR_PATH, $url));
            $url .= '?ver=' . $mtime;
            $html .= sprintf('<script type="text/javascript" src="%s"></script>' . "\n", $url);
        }

        foreach ($inlineJs as $content) {
            $html .= sprintf('<script type="text/javascript"%s>%s</script>' . "\n", $nonce, $content);
        }

        foreach ($inlineHead as $content) {
            $html .= $content . "\n";
        }

        if ($this->enableJqueryNoConflict && !$this->useRequireJs) {
            $html .= '<script type="text/javascript"' . $nonce . '>jQuery.noConflict(true);</script>' . "\n";
        }

        return $html;
    }
}
