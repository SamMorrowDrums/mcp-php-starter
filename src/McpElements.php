<?php

declare(strict_types=1);

namespace McpPhpStarter;

use Mcp\Capability\Attribute\McpTool;
use Mcp\Capability\Attribute\McpResource;
use Mcp\Capability\Attribute\McpResourceTemplate;
use Mcp\Capability\Attribute\McpPrompt;
use Mcp\Schema\Content\AudioContent;
use Mcp\Schema\Content\ImageContent;
use Mcp\Schema\Content\TextContent;
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
            openWorldHint: true
        ),
        icons: [new Icon('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><text y=".9em" font-size="90">🤖</text></svg>')]
    )]
    public function askLlm(RequestContext $context, string $prompt, int $maxTokens = 100): string
    {
        // Use the client's sampling capability to invoke their LLM
        $response = $context->getClientGateway()->sample(
            message: $prompt,
            maxTokens: $maxTokens
        );

        return match (true) {
            $response->content instanceof TextContent => $response->content->text,
            $response->content instanceof AudioContent => $response->content->data,
            $response->content instanceof ImageContent => $response->content->data,
            default => 'No response from LLM',
        };
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
            // Send progress notification to the client
            $context->getClientGateway()->progress(
                progress: $i + 1,
                total: $steps,
                message: "Processing step " . ($i + 1) . " of {$steps}"
            );
            usleep(200000); // 200ms per step (1 second total for faster CI)
        }
        
        return "Task \"{$taskName}\" completed successfully after {$steps} steps!";
    }

    /**
     * Dynamically load a bonus calculator tool.
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
        icons: [new Icon('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><text y=".9em" font-size="90">⚡</text></svg>')]
    )]
    public function loadBonusTool(): string
    {
        if ($this->bonusToolLoaded) {
            return "Bonus tool is already loaded! Use 'bonus_calculator' tool.";
        }
        
        $this->bonusToolLoaded = true;
        // Note: In the PHP SDK, dynamic tool loading would trigger
        // a tools/list_changed notification to clients
        return "Bonus tool loaded successfully! The 'bonus_calculator' tool is now available.";
    }

    /**
     * Calculate a bonus percentage on an amount.
     * This tool is dynamically loaded by load_bonus_tool.
     *
     * @param float $amount Base amount
     * @param float $percentage Bonus percentage (default: 10)
     * @return array<string, float> Bonus calculation result
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
        icons: [new Icon('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><text y=".9em" font-size="90">🔧</text></svg>')]
    )]
    public function bonusCalculator(float $amount, float $percentage = 10.0): array
    {
        $bonus = $amount * ($percentage / 100.0);
        return [
            'amount' => $amount,
            'percentage' => $percentage,
            'bonus' => $bonus,
            'total' => $amount + $bonus,
        ];
    }

    /**
     * Performs basic arithmetic operations.
     *
     * @param float $a The first number
     * @param float $b The second number
     * @param string $operation The operation (add, subtract, multiply, divide)
     * @return float|string The result or an error message
     */
    #[McpTool(
        name: 'calculate',
        annotations: new ToolAnnotations(
            title: 'Calculator',
            readOnlyHint: true,
            destructiveHint: false,
            idempotentHint: true,
            openWorldHint: false
        )
    )]
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
    #[McpTool(
        annotations: new ToolAnnotations(
            title: 'Echo',
            readOnlyHint: true,
            destructiveHint: false,
            idempotentHint: true,
            openWorldHint: false
        ),
        icons: [new Icon('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><text y=".9em" font-size="90">📢</text></svg>')]
    )]
    public function echo(string $message): string
    {
        return $message;
    }

    /**
     * Request user confirmation before proceeding with an action.
     * Demonstrates schema elicitation - displays a form for user input.
     *
     * @param string $action Description of the action to confirm
     * @param bool $destructive Whether the action is destructive
     * @return string Confirmation result message
     */
    public function confirmAction(string $action, bool $destructive = false): string
    {
        // Note: Full elicitation support requires the PHP SDK to implement it.
        // For now, we return a placeholder message demonstrating the intent.
        $type = $destructive ? 'DESTRUCTIVE' : 'standard';
        return "Confirmation requested for {$type} action: {$action}\n\n" .
               "Note: Full schema elicitation requires client support. " .
               "The action would display a confirmation dialog with:\n" .
               "- Confirm (boolean): Whether to proceed\n" .
               "- Reason (string, optional): Reason for the choice";
    }

    /**
     * Request feedback from the user via URL elicitation.
     * Opens a feedback form in the user's browser.
     *
     * @param string $question The question to ask the user
     * @return string Feedback URL and instructions
     */
    public function getFeedback(string $question): string
    {
        $feedbackUrl = 'https://github.com/SamMorrowDrums/mcp-starters/issues/new?template=workshop-feedback.yml';
        if (!empty($question)) {
            $feedbackUrl .= '&title=' . urlencode($question);
        }

        // Note: Full URL elicitation requires the PHP SDK to implement it.
        // For now, we return the URL for the user to visit manually.
        return "Please provide feedback on MCP Starters!\n\n" .
               "Question: {$question}\n\n" .
               "Feedback URL: {$feedbackUrl}\n\n" .
               "Note: Full URL elicitation would open this URL in your browser automatically.";
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
    // RESOURCE TEMPLATES
    // Resource templates allow parameterized resource URIs.
    // =========================================================================

    /**
     * Get a personalized greeting for a specific person.
     *
     * @param string $name The name of the person to greet
     * @return string Personalized greeting message
     */
    #[McpResourceTemplate(
        uriTemplate: 'greeting://{name}',
        name: 'Personalized Greeting',
        mimeType: 'text/plain'
    )]
    public function getPersonalizedGreeting(string $name): string
    {
        return "Hello, {$name}! Welcome to the MCP PHP Starter server. We're glad to have you here!";
    }

    /**
     * Get item data by ID using a resource template.
     *
     * @param string $id The item ID
     * @return array<string, mixed> Item data
     */
    #[McpResourceTemplate(
        uriTemplate: 'data://items/{id}',
        name: 'Item Data',
        mimeType: 'application/json'
    )]
    public function getItemData(string $id): array
    {
        // Simulated item data
        return [
            'id' => $id,
            'name' => "Item {$id}",
            'description' => "This is item {$id} retrieved via resource template",
            'created_at' => date('c'),
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
