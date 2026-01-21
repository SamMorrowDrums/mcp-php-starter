<?php

declare(strict_types=1);

namespace McpPhpStarter;

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
        $elements = new McpElements();
        
        return $builder
            // Tools with custom schemas (title + description on all properties)
            ->addTool(
                handler: [$elements, 'hello'],
                name: 'hello',
                description: 'Say hello to a person',
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
                handler: [$elements, 'getWeather'],
                name: 'get_weather',
                description: 'Get the current weather for a city',
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
                handler: [$elements, 'longTask'],
                name: 'long_task',
                description: 'Simulate a long-running task with progress updates',
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
                handler: [$elements, 'loadBonusTool'],
                name: 'load_bonus_tool',
                description: 'Dynamically register a new bonus tool',
                inputSchema: [
                    'type' => 'object',
                    'properties' => (object)[],
                    'required' => [],
                ]
            )
            ->addTool(
                handler: [$elements, 'askLlm'],
                name: 'ask_llm',
                description: 'Ask the connected LLM a question using sampling',
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
                handler: [$elements, 'confirmAction'],
                name: 'confirm_action',
                description: 'Request user confirmation before proceeding',
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
                handler: [$elements, 'getFeedback'],
                name: 'get_feedback',
                description: 'Request feedback from the user',
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
                handler: [$elements, 'getAbout'],
                uri: 'about://server',
                name: 'About',
                description: 'Information about this MCP server',
                mimeType: 'text/plain'
            )
            ->addResource(
                handler: [$elements, 'getExampleDocument'],
                uri: 'doc://example',
                name: 'Example Document',
                description: 'An example document resource',
                mimeType: 'text/plain'
            )
            
            // Resource Templates
            ->addResourceTemplate(
                handler: [$elements, 'getPersonalizedGreeting'],
                uriTemplate: 'greeting://{name}',
                name: 'Personalized Greeting',
                description: 'A personalized greeting for a specific person',
                mimeType: 'text/plain'
            )
            ->addResourceTemplate(
                handler: [$elements, 'getItemData'],
                uriTemplate: 'item://{id}',
                name: 'Item Data',
                description: 'Data for a specific item by ID',
                mimeType: 'application/json'
            )
            
            // Prompts
            ->addPrompt(
                handler: [$elements, 'greetPrompt'],
                name: 'greet',
                description: 'Generate a greeting message'
            )
            ->addPrompt(
                handler: [$elements, 'codeReviewPrompt'],
                name: 'code_review',
                description: 'Review code for potential improvements'
            );
    }

    /**
     * Get server instructions text for AI assistants.
     */
    public static function getInstructions(): string
    {
        return <<<INSTRUCTIONS
# MCP PHP Starter Server

A demonstration MCP server showcasing PHP SDK capabilities with canonical MCP interface.

## Available Tools

- **hello**: Say hello to a person
- **get_weather**: Get the current weather for a city
- **long_task**: Simulate a long-running task with progress updates
- **load_bonus_tool**: Dynamically register a new bonus tool
- **ask_llm**: Ask the connected LLM a question using sampling
- **confirm_action**: Request user confirmation before proceeding
- **get_feedback**: Request feedback from the user

## Available Resources

- **about://server**: Information about this MCP server
- **doc://example**: An example document resource

## Available Resource Templates

- **greeting://{name}**: A personalized greeting for a specific person
- **item://{id}**: Data for a specific item by ID

## Available Prompts

- **greet**: Generate a greeting message
- **code_review**: Review code for potential improvements

## Recommended Workflows

1. **Testing Connection**: Call `hello` with your name to verify the server is responding
2. **Weather Demo**: Call `get_weather` with a location to see structured output
3. **Long Task**: Call `long_task` to see progress reporting
INSTRUCTIONS;
    }
}
