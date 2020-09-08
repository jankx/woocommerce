<?php
namespace Jankx\Ecommerce;

use Jankx\Template\Template as JankxTemplate;

class Template {
    protected static $loader;

    public static function getTemplateInstance() {
        if (is_null(static::$loader)) {
            $templateDirectory = sprintf('%s/templates', dirname(JANKX_ECOMMERCE_FILE_LOADER));
            $directoryInTheme  = apply_filters('jankx_theme_template_directory_name', 'templates/ecommerce');
            $templateEngine    = apply_filters('jankx_ecommerce_template_engine_name', 'wordpress');

            // Create the template loader instance
            static::$loader = JankxTemplate::getLoader($templateDirectory, $directoryInTheme, $templateEngine);
        }
        return static::$loader;
    }

    public static function render() {
        return call_user_func_array(
            array(
                static::getTemplateInstance(),
                'render'
            ),
            func_get_args()
        );
    }
}
