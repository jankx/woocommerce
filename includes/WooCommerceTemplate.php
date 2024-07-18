<?php

namespace Jankx\WooCommerce;

use Jankx;
use Jankx\Template\Template;
use Jankx\TemplateEngine\Engines\WordPress;
use Jankx\TemplateEngine\Engines\Plates;

class WooCommerceTemplate
{
    const ENGINE_ID = 'jankx_woocommerce';

    protected static $engine;

    public static function getEngine()
    {
        if (is_null(static::$engine)) {
            $activeEngine = Jankx::getActiveTemplateEngine();
            $defaultTemplateDir = sprintf(
                '%s/%s',
                dirname(JANKX_WOOCOMMERCE_FILE_LOADER),
                in_array($activeEngine, array(
                    Plates::ENGINE_NAME,
                    WordPress::ENGINE_NAME,
                )) ? 'templates' : $activeEngine
            );

            $engine = Template::createEngine(
                static::ENGINE_ID,
                apply_filters('jankx/theme/template/directory', 'templates/woocommerce'),
                $defaultTemplateDir,
                $activeEngine
            );

            // Create the template engine instance
            static::$engine = &$engine;
        }

        return static::$engine;
    }

    public static function render($templateName, $data = array(), $echo = true)
    {
        $engine = static::getEngine();
        if (!$engine) {
            return;
        }

        return $engine->render(
            $templateName,
            $data,
            $echo
        );
    }

    public static function search($templateName)
    {
        $engine = static::getEngine();

        return $engine->searchTemplate($templateName);
    }
}
