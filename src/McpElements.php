<?php

declare(strict_types=1);

namespace McpPhpStarter;

use Mcp\Capability\Attribute\McpTool;
use Mcp\Capability\Attribute\McpResource;
use Mcp\Capability\Attribute\McpResourceTemplate;
use Mcp\Capability\Attribute\McpPrompt;
use Mcp\Schema\ToolAnnotations;
use Mcp\Schema\Icon;
use Mcp\Server\RequestContext;

/**
 * MCP PHP Starter - Elements
 *
 * All MCP capabilities (tools, resources, prompts) defined using attributes.
 * This demonstrates the attribute-based discovery pattern from the PHP SDK,
 * including advanced features like sampling, progress, icons, and annotations.
 *
 * @see https://modelcontextprotocol.io/
 */
class McpElements
{
    private bool $bonusToolLoaded = false;

    // =============================================================================
    // TOOL ANNOTATIONS - Every tool SHOULD have annotations for AI assistants
    //
    // WHY ANNOTATIONS MATTER:
    // Annotations enable MCP client applications to understand the risk level of
    // tool calls. Clients can use these hints to implement safety policies.
    //
    // ANNOTATION FIELDS:
    // - readOnlyHint: Tool only reads data, doesn't modify state
    // - destructiveHint: Tool can permanently delete or modify data
    // - idempotentHint: Repeated calls with same args have same effect
    // - openWorldHint: Tool accesses external systems (web, APIs, etc.)
    // =============================================================================

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
    #[McpTool(
        annotations: new ToolAnnotations(
            title: 'Say Hello',
            readOnlyHint: true,
            destructiveHint: false,
            idempotentHint: true,
            openWorldHint: false
        ),
        icons: [new Icon('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><text y=".9em" font-size="90">👋</text></svg>')]
    )]
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
    #[McpTool(
        name: 'get_weather',
        annotations: new ToolAnnotations(
            title: 'Get Weather',
            readOnlyHint: true,
            destructiveHint: false,
            idempotentHint: false,
            openWorldHint: false
        ),
        icons: [new Icon('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><text y=".9em" font-size="90">🌤️</text></svg>')]
    )]
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
     * Ask the connected LLM a question using sampling.
     *
     * @param RequestContext $context The request context for client communication
     * @param string $prompt The question or prompt for the LLM
     * @param int $maxTokens Maximum tokens in response (default: 100)
     * @return string The LLM response
     */
    #[McpTool(
        name: 'ask_llm',
        annotations: new ToolAnnotations(
            title: 'Ask LLM',
            readOnlyHint: true,
            destructiveHint: false,
            idempotentHint: false,
            openWorldHint: false
        ),
        icons: [new Icon('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><text y=".9em" font-size="90">🤖</text></svg>')]
    )]
    public function askLlm(RequestContext $context, string $prompt, int $maxTokens = 100): string
    {
        try {
            $result = $context->getClientGateway()->sample($prompt, $maxTokens);
            return "LLM Response: " . ($result->content->text ?? '[non-text response]');
        } catch (\Throwable $e) {
            return "Sampling not supported or failed: " . $e->getMessage();
        }
    }

    /**
     * A task that takes time and reports progress along the way.
     *
     * @param RequestContext $context The request context for progress notifications
     * @param string $taskName Name for this task
     * @return string Completion message
     */
    #[McpTool(
        name: 'long_task',
        annotations: new ToolAnnotations(
            title: 'Long Running Task',
            readOnlyHint: true,
            destructiveHint: false,
            idempotentHint: true,
            openWorldHint: false
        ),
        icons: [new Icon('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><text y=".9em" font-size="90">⏳</text></svg>')]
    )]
    public function longTask(RequestContext $context, string $taskName): string
    {
        $steps = 5;
        
        for ($i = 0; $i < $steps; $i++) {
            try {
                $context->getClientGateway()->progress(
                    ($i + 1) / $steps,
                    1.0,
                    "Step " . ($i + 1) . "/{$steps}"
                );
            } catch (\Throwable $e) {
                // Progress not supported, continue anyway
            }
            usleep(200000); // 200ms per step
        }
        
        return "Task \"{$taskName}\" completed successfully after {$steps} steps!";
    }

    /**
     * Dynamically loads a bonus tool that wasn't available at startup.
     *
     * @return string Status message
     */
    #[McpTool(
        name: 'load_bonus_tool',
        annotations: new ToolAnnotations(
            title: 'Load Bonus Tool',
            readOnlyHint: false,
            destructiveHint: false,
            idempotentHint: true,
            openWorldHint: false
        ),
        icons: [new Icon('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><text y=".9em" font-size="90">📦</text></svg>')]
    )]
    public function loadBonusTool(): string
    {
        if ($this->bonusToolLoaded) {
            return "Bonus tool is already loaded! Try calling 'bonus_calculator'.";
        }
        
        $this->bonusToolLoaded = true;
        return "Bonus tool 'bonus_calculator' has been loaded! The tools list has been updated.";
    }

    /**
     * A calculator that was dynamically loaded (available after load_bonus_tool).
     *
     * @param float $a The first number
     * @param float $b The second number
     * @param string $operation The operation (add, subtract, multiply, divide)
     * @return float|string The result or an error message
     */
    #[McpTool(
        name: 'bonus_calculator',
        annotations: new ToolAnnotations(
            title: 'Bonus Calculator',
            readOnlyHint: true,
            destructiveHint: false,
            idempotentHint: true,
            openWorldHint: false
        ),
        icons: [new Icon('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><text y=".9em" font-size="90">🧮</text></svg>')]
    )]
    public function bonusCalculator(float $a, float $b, string $operation): float|string
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
    #[McpTool(
        annotations: new ToolAnnotations(
            title: 'Echo',
            readOnlyHint: true,
            destructiveHint: false,
            idempotentHint: true,
            openWorldHint: false
        )
    )]
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
- Tools with structured output, icons, and annotations
- Resources (static, dynamic, and templates)
- Prompts with completions
- LLM Sampling (ask_llm tool)
- Progress notifications (long_task tool)
- Dynamic tool loading (load_bonus_tool)
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
                'sampling' => true,
                'progress' => true,
            ],
            'settings' => [
                'precision' => 2,
                'allow_negative' => true,
            ],
        ];
    }

    // =========================================================================
    // RESOURCE TEMPLATES
    // Resource templates allow parameterized URIs for dynamic resources.
    // =========================================================================

    /**
     * Get a data item by ID using a URI template.
     *
     * @param string $id The item ID
     * @return array<string, mixed> The item data
     */
    #[McpResourceTemplate(
        uriTemplate: 'data://items/{id}',
        name: 'Data Item',
        mimeType: 'application/json'
    )]
    public function getDataItem(string $id): array
    {
        return [
            'id' => $id,
            'name' => "Item {$id}",
            'description' => "This is data item with ID: {$id}",
            'created' => date('c'),
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
