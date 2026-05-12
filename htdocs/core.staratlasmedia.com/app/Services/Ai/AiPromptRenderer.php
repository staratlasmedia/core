<?php

namespace App\Services\Ai;

class AiPromptRenderer
{
    /**
     * @param array<string, mixed> $variables
     */
    public function render(string $template, array $variables): string
    {
        foreach ($variables as $key => $value) {
            $template = str_replace('{{ '.$key.' }}', (string) $value, $template);
            $template = str_replace('{{'.$key.'}}', (string) $value, $template);
        }

        return $template;
    }
}
