<?php
/**
 * Laravel Views to Single File Converter
 * Usage: php convert_views.php /path/to/your/laravel/project
 */

class LaravelViewsConverter
{
    protected $outputFile = 'all_views.txt';
    protected $excludedDirs = ['vendor', 'node_modules', 'storage', 'cache'];
    protected $allowedExtensions = ['.blade.php', '.php', '.html', '.css', '.js'];

    public function convert($projectPath)
    {
        $viewsPath = $projectPath . '/resources/views';
        
        if (!is_dir($viewsPath)) {
            echo "Error: Views directory not found at: $viewsPath\n";
            return false;
        }

        $output = "LARAVEL VIEWS CONVERSION\n";
        $output .= "Generated: " . date('Y-m-d H:i:s') . "\n";
        $output .= "Project: " . realpath($projectPath) . "\n";
        $output .= str_repeat("=", 80) . "\n\n";

        $this->processDirectory($viewsPath, $output, $viewsPath);

        file_put_contents($this->outputFile, $output);
        
        echo "Success! All views have been converted to: " . $this->outputFile . "\n";
        echo "Total file size: " . number_format(strlen($output)) . " bytes\n";
        
        return true;
    }

    protected function processDirectory($dir, &$output, $basePath)
    {
        $files = scandir($dir);
        
        foreach ($files as $file) {
            if ($file == '.' || $file == '..') continue;
            
            $fullPath = $dir . '/' . $file;
            $relativePath = str_replace($basePath . '/', '', $fullPath);
            
            // Skip excluded directories
            if (is_dir($fullPath)) {
                if (in_array($file, $this->excludedDirs)) continue;
                $this->processDirectory($fullPath, $output, $basePath);
                continue;
            }
            
            // Check file extension
            $includeFile = false;
            foreach ($this->allowedExtensions as $ext) {
                if (str_ends_with($file, $ext)) {
                    $includeFile = true;
                    break;
                }
            }
            
            if (!$includeFile) continue;
            
            // Add file to output
            $output .= "\n" . str_repeat("-", 60) . "\n";
            $output .= "FILE: " . $relativePath . "\n";
            $output .= str_repeat("-", 60) . "\n";
            
            $content = file_get_contents($fullPath);
            $output .= $content . "\n";
        }
    }
}

// CLI execution
if (php_sapi_name() === 'cli') {
    $projectPath = $argv[1] ?? getcwd();
    
    $converter = new LaravelViewsConverter();
    $converter->convert($projectPath);
}