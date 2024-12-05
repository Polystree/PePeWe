
<?php
class Template {
    private static $instance = null;
    private $variables = [];
    private $config;

    private function __construct() {
        $this->config = include(__DIR__ . '/../config/config.php');
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function render($template, $vars = []) {
        $this->variables = array_merge($this->variables, $vars);
        extract($this->variables);
        
        ob_start();
        include $this->config['paths']['templates'] . "/$template.php";
        $content = ob_get_clean();
        
        include $this->config['paths']['templates'] . "/base.php";
    }

    public function partial($template, $vars = []) {
        extract(array_merge($this->variables, $vars));
        include $this->config['paths']['templates'] . "/$template.php";
    }
}