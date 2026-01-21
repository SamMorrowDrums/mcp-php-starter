<?php

declare(strict_types=1);

namespace McpPhpStarter;

/**
 * MCP PHP Starter - Elements
 *
 * All MCP capabilities (tools, resources, prompts) defined as methods.
 * These will be manually registered in the server configuration with
 * proper schemas that include both title and description fields.
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
     * Say hello to a person.
     *
     * @param string $name Name of the person to greet
     * @return string The greeting message
     */
    public function hello(string $name): string
    {
        return "Hello, {$name}! Welcome to MCP.";
    }

    /**
     * Get the current weather for a city.
     *
     * @param string $city City name to get weather for
     * @return array<string, mixed> Weather data including temperature, conditions, humidity
     */
    public function getWeather(string $city): array
    {
        $conditions = ['sunny', 'cloudy', 'rainy', 'windy'];

        return [
            'location' => $city,
            'temperature' => rand(15, 35),
            'unit' => 'celsius',
            'conditions' => $conditions[array_rand($conditions)],
            'humidity' => rand(40, 80),
        ];
    }

    /**
     * Simulate a long-running task with progress updates.
     *
     * @param string $taskName Name for this task
     * @param int $steps Number of steps to simulate
     * @return string Completion message
     */
    public function longTask(string $taskName, int $steps = 5): string
    {
        for ($i = 0; $i < $steps; $i++) {
            // In a real implementation, progress notifications would be sent
            usleep(200000); // 200ms per step (1 second total for faster CI)
        }

        return "Task \"{$taskName}\" completed successfully after {$steps} steps!";
    }

    /**
     * Dynamically register a new bonus tool.
     *
     * @return string Confirmation message
     */
    public function loadBonusTool(): string
    {
        $this->bonusToolLoaded = true;
        return 'Bonus tool has been successfully loaded and registered!';
    }

    /**
     * Ask the connected LLM a question using sampling.
     *
     * @param string $prompt The question or prompt to send to the LLM
     * @param int $maxTokens Maximum tokens in response
     * @return string The LLM's response
     */
    public function askLlm(string $prompt, int $maxTokens = 100): string
    {
        // Simulated LLM response
        return "This is a simulated response to: \"{$prompt}\". In a real implementation, this would use the MCP sampling feature to query the connected LLM. (Max tokens: {$maxTokens})";
    }

    /**
     * Request user confirmation before proceeding.
     *
     * @param string $action Description of the action to confirm
     * @param bool $destructive Whether the action is destructive
     * @return array<string, mixed> Confirmation result
     */
    public function confirmAction(string $action, bool $destructive = false): array
    {
        // In a real implementation, this would prompt the user for confirmation
        return [
            'action' => $action,
            'destructive' => $destructive,
            'confirmed' => true,
            'message' => "User confirmed: {$action}",
        ];
    }

    /**
     * Request feedback from the user.
     *
     * @param string $question The question to ask the user
     * @return array<string, mixed> User's feedback
     */
    public function getFeedback(string $question): array
    {
        // In a real implementation, this would prompt the user for feedback
        return [
            'question' => $question,
            'feedback' => "This is simulated user feedback for: {$question}",
            'timestamp' => date('c'),
        ];
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
     * An example document resource.
     *
     * @return string Document content
     */
    public function getExampleDocument(): string
    {
        return <<<TEXT
# Example Document

This is an example document served as an MCP resource.

## Features

- Demonstrates resource capabilities
- Shows structured content delivery
- Provides example data

## More Information

Visit https://modelcontextprotocol.io for documentation.
TEXT;
    }

    /**
     * A personalized greeting for a specific person.
     *
     * @param string $name The name of the person to greet
     * @return string Personalized greeting
     */
    public function getPersonalizedGreeting(string $name): string
    {
        return "Hello, {$name}! This is your personalized greeting from the MCP PHP server. Have a wonderful day!";
    }

    /**
     * Data for a specific item by ID.
     *
     * @param string $id The item ID
     * @return array<string, mixed> Item data
     */
    public function getItemData(string $id): array
    {
        return [
            'id' => $id,
            'name' => "Item {$id}",
            'description' => "This is a dynamically generated item with ID: {$id}",
            'timestamp' => date('c'),
            'properties' => [
                'color' => 'blue',
                'size' => 'medium',
                'quantity' => rand(1, 100),
            ],
        ];
    }

    // =========================================================================
    // PROMPTS
    // Prompts are pre-configured message templates the client can use.
    // =========================================================================

    /**
     * Generate a greeting message.
     *
     * @param string $name Name of the person to greet
     * @param string|null $style The greeting style (formal, casual, enthusiastic)
     * @return string The prompt text
     */
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
     * Review code for potential improvements.
     *
     * @param string $code The code to review
     * @param string $language Programming language
     * @param string|null $focus What to focus on (security, performance, readability, all)
     * @return string The prompt text
     */
    public function codeReviewPrompt(string $code, string $language = 'php', ?string $focus = 'all'): string
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
