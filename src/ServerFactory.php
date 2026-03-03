<?php

declare(strict_types=1);

namespace McpPhpStarter;

use Mcp\Schema\ServerCapabilities;
use Mcp\Schema\ToolAnnotations;
use Mcp\Server\Builder;

/**
 * Factory for creating configured MCP servers with canonical interface.
 */
class ServerFactory
{
    /**
     * Configure a server builder with all capabilities using manual registration.
     * This ensures all tools have proper schemas with title and description fields.
     */
    public static function configureBuilder(Builder $builder): Builder
    {
        return $builder
            ->setCapabilities(new ServerCapabilities(
                tools: true,
                toolsListChanged: true,
                resources: true,
                resourcesSubscribe: false,
                resourcesListChanged: false,
                prompts: true,
                promptsListChanged: false,
                logging: true,
                completions: true,
            ))
            // Tools with custom schemas (title + description on all properties)
            ->addTool(
                handler: [McpElements::class, 'hello'],
                name: 'hello',
                description: 'Say hello to a person',
                annotations: new ToolAnnotations(
                    readOnlyHint: true,
                    destructiveHint: false,
                    idempotentHint: true,
                    openWorldHint: false
                ),
                inputSchema: [
                    'type' => 'object',
                    'properties' => [
                        'name' => [
                            'type' => 'string',
                            'title' => 'Name',
                            'description' => 'Name of the person to greet',
                        ],
                    ],
                    'required' => ['name'],
                ]
            )
            ->addTool(
                handler: [McpElements::class, 'getWeather'],
                name: 'get_weather',
                description: 'Get the current weather for a city',
                annotations: new ToolAnnotations(
                    readOnlyHint: true,
                    destructiveHint: false,
                    idempotentHint: false,
                    openWorldHint: false
                ),
                inputSchema: [
                    'type' => 'object',
                    'properties' => [
                        'city' => [
                            'type' => 'string',
                            'title' => 'City',
                            'description' => 'City name to get weather for',
                        ],
                    ],
                    'required' => ['city'],
                ]
            )
            ->addTool(
                handler: [McpElements::class, 'longTask'],
                name: 'long_task',
                description: 'Simulate a long-running task with progress updates',
                annotations: new ToolAnnotations(
                    readOnlyHint: true,
                    destructiveHint: false,
                    idempotentHint: true,
                    openWorldHint: false
                ),
                inputSchema: [
                    'type' => 'object',
                    'properties' => [
                        'taskName' => [
                            'type' => 'string',
                            'title' => 'Task Name',
                            'description' => 'Name for this task',
                        ],
                        'steps' => [
                            'type' => 'integer',
                            'title' => 'Steps',
                            'description' => 'Number of steps to simulate',
                            'default' => 5,
                        ],
                    ],
                    'required' => ['taskName'],
                ]
            )
            ->addTool(
                handler: [McpElements::class, 'loadBonusTool'],
                name: 'load_bonus_tool',
                description: 'Dynamically register a new bonus tool',
                annotations: new ToolAnnotations(
                    readOnlyHint: false,
                    destructiveHint: false,
                    idempotentHint: true,
                    openWorldHint: false
                ),
                inputSchema: [
                    'type' => 'object',
                    'properties' => (object)[],
                    'required' => [],
                ]
            )
            ->addTool(
                handler: [McpElements::class, 'askLlm'],
                name: 'ask_llm',
                description: 'Ask the connected LLM a question using sampling',
                annotations: new ToolAnnotations(
                    readOnlyHint: true,
                    destructiveHint: false,
                    idempotentHint: false,
                    openWorldHint: false
                ),
                inputSchema: [
                    'type' => 'object',
                    'properties' => [
                        'prompt' => [
                            'type' => 'string',
                            'title' => 'Prompt',
                            'description' => 'The question or prompt to send to the LLM',
                        ],
                        'maxTokens' => [
                            'type' => 'integer',
                            'title' => 'Max Tokens',
                            'description' => 'Maximum tokens in response',
                            'default' => 100,
                        ],
                    ],
                    'required' => ['prompt'],
                ]
            )
            ->addTool(
                handler: [McpElements::class, 'confirmAction'],
                name: 'confirm_action',
                description: 'Request user confirmation before proceeding',
                annotations: new ToolAnnotations(
                    readOnlyHint: true,
                    destructiveHint: false,
                    idempotentHint: false,
                    openWorldHint: false
                ),
                inputSchema: [
                    'type' => 'object',
                    'properties' => [
                        'action' => [
                            'type' => 'string',
                            'title' => 'Action',
                            'description' => 'Description of the action to confirm',
                        ],
                        'destructive' => [
                            'type' => 'boolean',
                            'title' => 'Destructive',
                            'description' => 'Whether the action is destructive',
                            'default' => false,
                        ],
                    ],
                    'required' => ['action'],
                ]
            )
            ->addTool(
                handler: [McpElements::class, 'getFeedback'],
                name: 'get_feedback',
                description: 'Request feedback from the user',
                annotations: new ToolAnnotations(
                    readOnlyHint: true,
                    destructiveHint: false,
                    idempotentHint: false,
                    openWorldHint: true
                ),
                inputSchema: [
                    'type' => 'object',
                    'properties' => [
                        'question' => [
                            'type' => 'string',
                            'title' => 'Question',
                            'description' => 'The question to ask the user',
                        ],
                    ],
                    'required' => ['question'],
                ]
            )

            // Resources
            ->addResource(
                handler: [McpElements::class, 'getAbout'],
                uri: 'about://server',
                name: 'Server Info',
                description: 'Information about this MCP server',
                mimeType: 'text/plain'
            )
            ->addResource(
                handler: [McpElements::class, 'getExampleDocument'],
                uri: 'doc://example',
                name: 'Example Document',
                description: 'An example document resource',
                mimeType: 'text/plain'
            )

            // Resource Templates
            ->addResourceTemplate(
                handler: [McpElements::class, 'getPersonalizedGreeting'],
                uriTemplate: 'greeting://{name}',
                name: 'Personalized Greeting',
                description: 'A personalized greeting for a specific person',
                mimeType: 'text/plain'
            )
            ->addResourceTemplate(
                handler: [McpElements::class, 'getItemData'],
                uriTemplate: 'item://{id}',
                name: 'Item Data',
                description: 'Data for a specific item by ID',
                mimeType: 'application/json'
            )

            // Prompts
            ->addPrompt(
                handler: [McpElements::class, 'greetPrompt'],
                name: 'greet',
                description: 'Generate a greeting message',
                meta: ['title' => 'Greeting Prompt']
            )
            ->addPrompt(
                handler: [McpElements::class, 'codeReviewPrompt'],
                name: 'code_review',
                description: 'Review code for potential improvements',
                meta: ['title' => 'Code Review']
            );
    }

    /**
     * Get server instructions text for AI assistants.
     */
    public static function getInstructions(): string
    {
        return <<<INSTRUCTIONS
# MCP PHP Starter Server

A demonstration MCP server showcasing PHP SDK capabilities.

## Available Tools

### Greeting & Demos
- **hello**: Simple greeting - use to test connectivity
- **get_weather**: Returns simulated weather data
- **long_task**: Demonstrates progress reporting (takes ~5 seconds)

### LLM Interaction
- **ask_llm**: Invoke LLM sampling to ask questions (requires client support)

### Dynamic Features
- **load_bonus_tool**: Dynamically adds a calculator tool at runtime
- **bonus_calculator**: Available after calling load_bonus_tool

### Elicitation (User Input)
- **confirm_action**: Demonstrates schema elicitation - requests user confirmation
- **get_feedback**: Demonstrates URL elicitation - opens feedback form in browser

## Available Resources

- **about://server**: Server information
- **doc://example**: Sample markdown document
- **greeting://{name}**: Personalized greeting template
- **item://{id}**: Item data by ID

## Available Prompts

- **greet**: Generates a personalized greeting
- **code_review**: Structured code review prompt

## Recommended Workflows

1. **Testing Connection**: Call `hello` with your name to verify the server is responding
2. **Weather Demo**: Call `get_weather` with a location to see structured output
3. **Progress Demo**: Call `long_task` to see progress notifications
4. **Dynamic Loading**: Call `load_bonus_tool`, then refresh tools to see `bonus_calculator`
5. **Elicitation Demo**: Call `confirm_action` to see user confirmation flow
6. **URL Elicitation**: Call `get_feedback` to open a feedback form

## Tool Annotations

All tools include annotations indicating:
- Whether they modify state (readOnlyHint)
- If they're safe to retry (idempotentHint)
- Whether they access external systems (openWorldHint)

Use these hints to make informed decisions about tool usage.
INSTRUCTIONS;
    }
}
