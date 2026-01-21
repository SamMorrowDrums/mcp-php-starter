# MCP PHP Starter

[![CI](https://github.com/SamMorrowDrums/mcp-php-starter/actions/workflows/ci.yml/badge.svg)](https://github.com/SamMorrowDrums/mcp-php-starter/actions/workflows/ci.yml)
[![PHP](https://img.shields.io/badge/PHP-8.2%2B-777BB4?logo=php&logoColor=white)](https://www.php.net/)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
[![MCP](https://img.shields.io/badge/MCP-Model%20Context%20Protocol-purple)](https://modelcontextprotocol.io/)

A feature-complete Model Context Protocol (MCP) server template in PHP. This starter demonstrates all major MCP features with clean, production-ready code.

## 📚 Documentation

- [Model Context Protocol](https://modelcontextprotocol.io/)
- [PHP SDK](https://github.com/modelcontextprotocol/php-sdk)
- [Building MCP Servers](https://modelcontextprotocol.io/docs/develop/build-server)

## ✨ Features

| Category | Feature | Description |
|----------|---------|-------------|
| **Tools** | `hello` | Say hello to a person |
| | `get_weather` | Get the current weather for a city |
| | `long_task` | Simulate a long-running task with progress updates |
| | `load_bonus_tool` | Dynamically register a new bonus tool |
| | `ask_llm` | Ask the connected LLM a question using sampling |
| | `confirm_action` | Request user confirmation before proceeding |
| | `get_feedback` | Request feedback from the user |
| **Resources** | `about://server` | Information about this MCP server |
| | `doc://example` | An example document resource |
| **Resource Templates** | `greeting://{name}` | A personalized greeting for a specific person |
| | `item://{id}` | Data for a specific item by ID |
| **Prompts** | `greet` | Generate a greeting message |
| | `code_review` | Review code for potential improvements |

## 🚀 Quick Start

### Prerequisites

- [PHP 8.2+](https://www.php.net/)
- [Composer](https://getcomposer.org/)

### Installation

```bash
# Clone the repository
git clone https://github.com/SamMorrowDrums/mcp-php-starter.git
cd mcp-php-starter

# Install dependencies
composer install
```

### Running the Server

**stdio transport** (for local development):
```bash
composer start:stdio
# or
php bin/server-stdio.php
```

**HTTP transport** (for remote/web deployment):
```bash
composer start:http
# Server runs on http://localhost:3000
```

## 🔧 VS Code Integration

This project includes VS Code configuration for seamless development:

1. Open the project in VS Code
2. The MCP configuration is in `.vscode/mcp.json`
3. Test the server using VS Code's MCP tools

## 📁 Project Structure

```
.
├── bin/
│   ├── server-stdio.php   # stdio transport entrypoint
│   └── server-http.php    # HTTP transport entrypoint
├── public/
│   └── index.php          # HTTP web entrypoint
├── src/
│   └── McpElements.php    # Tools, resources, and prompts
├── tests/
│   └── ...
├── .vscode/
│   └── mcp.json           # MCP server configuration
├── composer.json
├── server.json            # MCP discovery configuration
└── README.md
```

## 🛠️ Development

```bash
# Install dependencies
composer install

# Run code style check
composer cs-check

# Fix code style
composer cs-fix

# Run tests
composer test
```

## 🔍 MCP Inspector

The [MCP Inspector](https://modelcontextprotocol.io/docs/tools/inspector) is an essential development tool for testing and debugging MCP servers.

### Running Inspector

```bash
npx @modelcontextprotocol/inspector php bin/server-stdio.php
```

### What Inspector Provides

- **Tools Tab**: List and invoke all registered tools with parameters
- **Resources Tab**: Browse and read resources
- **Prompts Tab**: View and test prompt templates
- **Logs Tab**: See JSON-RPC messages between client and server

## 📖 Feature Examples

### Tools with Custom Schemas

All tools are manually registered with custom input schemas that include both `title` and `description` fields for cross-language consistency:

```php
// In ServerFactory.php
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
```

### Resources

```php
->addResource(
    handler: [$elements, 'getAbout'],
    uri: 'about://server',
    name: 'About',
    description: 'Information about this MCP server',
    mimeType: 'text/plain'
)
```

### Resource Templates

```php
->addResourceTemplate(
    handler: [$elements, 'getPersonalizedGreeting'],
    uriTemplate: 'greeting://{name}',
    name: 'Personalized Greeting',
    description: 'A personalized greeting for a specific person',
    mimeType: 'text/plain'
)
```

### Prompts

```php
->addPrompt(
    handler: [$elements, 'greetPrompt'],
    name: 'greet',
    description: 'Generate a greeting message'
)
```

## 🔐 Environment Variables

| Variable | Description | Default |
|----------|-------------|---------|
| `PORT` | HTTP server port | `3000` |

## 🤝 Contributing

Contributions welcome! Please ensure your changes maintain feature parity with other language starters.

## 📄 License

MIT License - see [LICENSE](LICENSE) for details.
