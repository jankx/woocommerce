<?php

/**
 * Mock WP_Term class for testing
 */
class WP_Term
{
    public $term_id;
    public $name;
    public $slug;
    public $term_group;
    public $term_taxonomy_id;
    public $taxonomy;
    public $description;
    public $parent;
    public $count;
    public $filter;

    public function __construct($term_id, $taxonomy = 'category')
    {
        $this->term_id = $term_id;
        $this->taxonomy = $taxonomy;
        $this->name = 'Test Term ' . $term_id;
        $this->slug = 'test-term-' . $term_id;
        $this->term_group = 0;
        $this->term_taxonomy_id = $term_id;
        $this->description = '';
        $this->parent = 0;
        $this->count = rand(5, 50);
        $this->filter = 'raw';
    }

    public function to_array()
    {
        return get_object_vars($this);
    }
}

/**
 * Mock WP_Error class
 */
class WP_Error
{
    protected $errors = [];
    protected $error_data = [];

    public function __construct($code = '', $message = '', $data = '')
    {
        if (empty($code)) {
            return;
        }

        $this->errors[$code][] = $message;

        if (!empty($data)) {
            $this->error_data[$code] = $data;
        }
    }

    public function get_error_codes()
    {
        return array_keys($this->errors);
    }

    public function get_error_code()
    {
        $codes = $this->get_error_codes();
        return empty($codes) ? '' : $codes[0];
    }

    public function get_error_messages($code = '')
    {
        if (empty($code)) {
            $all_messages = [];
            foreach ((array) $this->errors as $code => $messages) {
                $all_messages = array_merge($all_messages, $messages);
            }
            return $all_messages;
        }

        return $this->errors[$code] ?? [];
    }

    public function get_error_message($code = '')
    {
        if (empty($code)) {
            $code = $this->get_error_code();
        }
        $messages = $this->get_error_messages($code);
        return empty($messages) ? '' : $messages[0];
    }
}

