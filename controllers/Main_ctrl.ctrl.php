<?php
class Main_ctrl extends DB_ctrl
{
    public $post;
    public $get;
    public $files;
    public function __construct()
    {
        $this->post = obj($_POST);
        $this->get = obj($_GET);
        $this->files = isset($_FILES) ? obj($_FILES) : null;
    }
    public function render_layout($context = null, $layout = "apps/games/layouts/main.php")
    {
        import($layout, $context);
    }
}
