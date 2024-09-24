<?php

    namespace Rapidmail;

    /**
     * rapidmail simple template class
     */
    class Template {

        /**
         * @var array
         */
        private $vars = [];

        /**
         * Assign value to template
         *
         * @param string|array $varOrValuesArray
         * @param null|mixed $value
         */
        public function assign($varOrValuesArray, $value = NULL) {

            if (\is_array($varOrValuesArray)) {
                $this->vars = \array_replace($this->vars, $varOrValuesArray);
            } else {
                $this->vars[$varOrValuesArray] = $value;
            }

        }

        /**
         * Render template and display
         *
         * @param string $template
         */
        public function display($template) {
            \extract($this->vars);
            require (\plugin_dir_path(__DIR__) . 'templates/' . $template . '.phtml');
        }

        /**
         * Render template and return
         *
         * @param string $template
         * @return string
         */
        public function render($template) {

            \ob_start();
            $this->display($template);
            $content = \ob_get_contents();
            \ob_end_clean();
            return $content;

        }

    }