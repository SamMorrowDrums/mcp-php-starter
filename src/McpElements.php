<?php

declare(strict_types=1);

namespace McpPhpStarter;

use Mcp\Capability\Attribute\McpTool;
use Mcp\Capability\Attribute\McpResource;
use Mcp\Capability\Attribute\McpPrompt;

/**
 * MCP PHP Starter - Elements
 *
 * All MCP capabilities (tools, resources, prompts) defined using attributes.
 * This demonstrates the attribute-based discovery pattern from the PHP SDK.
 *
 * @see https://modelcontextprotocol.io/
 */
class McpElements
{
    private bool $bonusToolLoaded = false;

    // =========================================================================
    // TOOLS
    // Tools are functions that the client can invoke to perform actions.
    // =========================================================================

    /**
     * A friendly greeting tool that says hello to someone.
     *
     * @param string $name The name to greet
     * @return string The greeting message
     */
    #[McpTool]
    public function hello(string $name): string
    {
        return "Hello, {$name}! Welcome to MCP.";
    }

    /**
     * Get current weather for a location (simulated).
     *
     * @param string $location City name or coordinates
     * @return array<string, mixed> Weather data including temperature, conditions, humidity
     */
    #[McpTool(name: 'get_weather')]
    public function getWeather(string $location): array
    {
        $conditions = ['sunny', 'cloudy', 'rainy', 'windy'];
        
        return [
            'location' => $location,
            'temperature' => rand(15, 35),
            'unit' => 'celsius',
            'conditions' => $conditions[array_rand($conditions)],
            'humidity' => rand(40, 80),
        ];
    }

    /**
     * A task that takes time and reports progress along the way.
     *
     * @param string $taskName Name for this task
     * @return string Completion message
     */
    #[McpTool(name: 'long_task')]
    public function longTask(string $taskName): string
    {
        $steps = 5;
        
        for ($i = 0; $i < $steps; $i++) {
            // In a real implementation, progress notifications would be sent
            usleep(200000); // 200ms per step (1 second total for faster CI)
        }
        
        return "Task \"{$taskName}\" completed successfully after {$steps} steps!";
    }

    /**
     * Performs basic arithmetic operations.
     *
     * @param float $a The first number
     * @param float $b The second number
     * @param string $operation The operation (add, subtract, multiply, divide)
     * @return float|string The result or an error message
     */
    #[McpTool(name: 'calculate')]
    public function calculate(float $a, float $b, string $operation): float|string
    {
        return match($operation) {
            'add' => $a + $b,
            'subtract' => $a - $b,
            'multiply' => $a * $b,
            'divide' => $b != 0 ? $a / $b : 'Error: Division by zero',
            default => 'Error: Unknown operation. Use add, subtract, multiply, or divide.'
        };
    }

    /**
     * Echo back the provided message.
     *
     * @param string $message The message to echo back
     * @return string The echoed message
     */
    #[McpTool]
    public function echo(string $message): string
    {
        return $message;
    }

    // =========================================================================
    // RESOURCES
    // Resources expose data to the client that can be read.
    // =========================================================================

    /**
     * Information about this MCP server.
     *
     * @return string Server information
     */
    #[McpResource(
        uri: 'info://about',
        name: 'About',
        mimeType: 'text/plain'
    )]
    public function getAbout(): string
    {
        return <<<TEXT
MCP PHP Starter v1.0.0

This is a feature-complete MCP server demonstrating:
- Tools with structured output
- Resources (static and dynamic)
- Prompts with completions
- Multiple transport options (stdio, HTTP)

For more information, visit: https://modelcontextprotocol.io
TEXT;
    }

    /**
     * An example markdown document.
     *
     * @return string Markdown content
     */
    #[McpResource(
        uri: 'doc://example',
        name: 'Example Document',
        mimeType: 'text/markdown'
    )]
    public function getExampleDocument(): string
    {
        return <<<MARKDOWN
# Example Document

This is an example markdown document served as an MCP resource.

## Features

- **Bold text** and *italic text*
- Lists and formatting
- Code blocks

```php
<?php
\$hello = "world";
```

## Links

- [MCP Documentation](https://modelcontextprotocol.io)
- [PHP SDK](https://github.com/modelcontextprotocol/php-sdk)
MARKDOWN;
    }

    /**
     * Server configuration settings.
     *
     * @return array<string, mixed> Configuration data
     */
    #[McpResource(
        uri: 'config://settings',
        name: 'Server Settings',
        mimeType: 'application/json'
    )]
    public function getSettings(): array
    {
        return [
            'version' => '1.0.0',
            'name' => 'mcp-php-starter',
            'capabilities' => [
                'tools' => true,
                'resources' => true,
                'prompts' => true,
            ],
            'settings' => [
                'precision' => 2,
                'allow_negative' => true,
            ],
        ];
    }

    // =========================================================================
    // PROMPTS
    // Prompts are pre-configured message templates the client can use.
    // =========================================================================

    /**
     * Generate a greeting in a specific style.
     *
     * @param string $name Name of the person to greet
     * @param string|null $style The greeting style (formal, casual, enthusiastic)
     * @return string The prompt text
     */
    #[McpPrompt(name: 'greet')]
    public function greetPrompt(string $name, ?string $style = 'casual'): string
    {
        $styles = [
            'formal' => "Please compose a formal, professional greeting for {$name}.",
            'casual' => "Write a casual, friendly hello to {$name}.",
            'enthusiastic' => "Create an excited, enthusiastic greeting for {$name}!",
        ];

        return $styles[$style] ?? $styles['casual'];
    }

    /**
     * Request a code review with specific focus areas.
     *
     * @param string $code The code to review
     * @param string $language Programming language
     * @param string|null $focus What to focus on (security, performance, readability, all)
     * @return string The prompt text
     */
    #[McpPrompt(name: 'code_review')]
    public function codeReviewPrompt(string $code, string $language, ?string $focus = 'all'): string
    {
        $focusInstructions = [
            'security' => 'Focus on security vulnerabilities and potential exploits.',
            'performance' => 'Focus on performance optimizations and efficiency issues.',
            'readability' => 'Focus on code clarity, naming, and maintainability.',
            'all' => 'Provide a comprehensive review covering security, performance, and readability.',
        ];

        $instruction = $focusInstructions[$focus] ?? $focusInstructions['all'];

        return <<<PROMPT
Please review the following {$language} code. {$instruction}

```{$language}
{$code}
```
PROMPT;
    }
}
