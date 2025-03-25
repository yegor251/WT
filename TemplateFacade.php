<?php

class TemplateFacade
{
    private $cacheDir;

    public function __construct($cacheDir = __DIR__ . '/cache'){
        $this->cacheDir = $cacheDir;
        if(!file_exists($this->cacheDir)){
            mkdir($this->cacheDir,0777,true);
        }
    }

    /**
     * Рендерит шаблон с данными
     *
     * @param string $templatePath - Путь к шаблону
     * @param array $data - Данные для подстановки
     * @return string - Готовый HTML
     * @throws Exception
     */
    public function render(string $templatePath, array $data): string
    {
        if(!file_exists($templatePath)){
            throw new Exception("Temp1late $templatePath not found");
        }

        $cacheFile = $this->cacheDir . DIRECTORY_SEPARATOR . md5($templatePath) . '.php';
        if(file_exists($cacheFile) && filemtime($cacheFile) <= filemtime($templatePath)){
            return $this->executeTemplate($cacheFile, $data);
        }

        $html = $this->compileTemplate($templatePath);

        file_put_contents($cacheFile, $html);

        return $this->executeTemplate($cacheFile, $data);
    }

    /**
     * Компилирует шаблон в PHP-код
     */
    private function compileTemplate($templatePath) {
        $html = file_get_contents($templatePath);

        $html = preg_replace_callback('/{{ if (.*?) }}(.*?)({{ else }}(.*?))?{{ endif }}/s', function ($matches) {
            $condition = trim($matches[1]);
            $ifContent = $matches[2];
            $elseContent = $matches[4] ?? '';
            return "<?php if ($condition): ?>$ifContent<?php else: ?>$elseContent<?php endif; ?>";
        }, $html);

        $html = preg_replace_callback('/{{ foreach (.*?) }}(.*?){{ endforeach }}/s', function ($matches) {
            $iteration = trim($matches[1]);
            $content = $matches[2];
            return "<?php foreach ($iteration): ?>$content<?php endforeach; ?>";
        }, $html);

        return preg_replace_callback('/{{\s*(.+?)\s*}}/', function ($matches) {
            $expression = trim($matches[1]);
            return "<?php echo htmlspecialchars($expression, ENT_QUOTES, 'UTF-8'); ?>";
        }, $html);
    }

    private function executeTemplate($cacheFile,array $data) {
        extract($data);
        ob_start();
        include $cacheFile;
        return ob_get_clean();
    }


}