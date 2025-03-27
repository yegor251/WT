<?php

class TemplateFacade
{
    public function render(string $templatePath, array $data): string
    {
        if (!file_exists($templatePath)) {
            throw new Exception("Template {$templatePath} not found");
        }

        $templateContent = file_get_contents($templatePath);
        $phpCode = $this->convertToPhp($templateContent);
        return $this->generateOutput($phpCode, $data);
    }

    private function convertToPhp(string $template): string
    {
        $replacements = [
            // Обработка if-else условий
            '/{{ if (.*?) }}(.*?)({{ else }}(.*?))?{{ endif }}/s' =>
                function($m) {
                    $else = isset($m[4]) ? "<?php else: ?>{$m[4]}" : '';
                    return "<?php if ({$m[1]}): ?>{$m[2]}{$else}<?php endif; ?>";
                },

            // Обработка foreach циклов
            '/{{ foreach (.*?) }}(.*?){{ endforeach }}/s' =>
                function($m) {
                    return "<?php foreach ({$m[1]}): ?>{$m[2]}<?php endforeach; ?>";
                },

            // Обработка переменных
            '/{{\s*(.+?)\s*}}/' =>
                function($m) {
                    return "<?php echo htmlspecialchars({$m[1]}, ENT_QUOTES, 'UTF-8'); ?>";
                }
        ];

        foreach ($replacements as $pattern => $callback) {
            $template = preg_replace_callback($pattern, $callback, $template);
        }

        return $template;
    }

    private function generateOutput(string $phpCode, array $data): string
    {
        extract($data, EXTR_SKIP);
        ob_start();

        try {
            eval('?>' . $phpCode);
        } catch (Throwable $e) {
            ob_end_clean();
            throw new Exception("Template rendering failed: " . $e->getMessage());
        }

        return ob_get_clean();
    }
}