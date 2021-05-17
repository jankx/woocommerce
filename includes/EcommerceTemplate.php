<?php
namespace Jankx\Ecommerce;

use Jankx;
use Jankx\Template\Template;

class EcommerceTemplate
{
    const ENGINE_ID = 'jankx_ecommerce';

    protected static $engine;

    public static function getEngine()
    {
        if (is_null(static::$engine)) {
            $engine = Template::createEngine(
                static::ENGINE_ID,
                apply_filters('jankx_theme_template_directory_name', 'templates/ecommerce'),
                sprintf('%s/templates', dirname(JANKX_ECOMMERCE_FILE_LOADER)),
                Jankx::getActiveTemplateEngine()
            );

            // Create the template engine instance
            static::$engine = &$engine;
        }
        return static::$engine;
    }

    public static function render()
    {
        return call_user_func_array(
            array(
                static::getEngine(),
                'render'
            ),
            func_get_args()
        );
    }

    public static function search()
    {
        return call_user_func_array(
            array(
                static::getEngine(),
                'searchTemplate'
            ),
            func_get_args()
        );
    }
}
