# ci-console

> **This is a personal exploration, not an official project.**
>
> This repo is a sketch. I used it to try out ideas for a standalone CLI library. It is **not** an official CodeIgniter package. It is **not** a work-in-progress for any CodeIgniter release. It is not maintained, and it may never be finished. Please don't build on it or treat it as a sign of where CodeIgniter is heading.

## What it is

`ci-console` is an attempt at a small, framework-agnostic PHP library for building command-line tools. The idea was to have something like CodeIgniter's `spark` that could run on its own, without the rest of the framework.

It has no runtime dependencies. It needs PHP 8.4 or newer.

## What is in here

### Input parsing (`src/Parse`)

This is the most complete part of the code.

- `Tokenizer` splits `$argv` into tokens. It handles single and double quotes and keeps empty quoted strings. It treats `--` as "stop parsing options."
- `Input` turns the tokens into a command name, positional arguments, and options. It supports:
  - long options: `--name=value`, `--name value`, and `--flag`
  - short options: `-f value` and grouped flags like `-vf`
  - boolean-like values (`true`/`false`, `1`/`0`, `yes`/`no`), which become real booleans
- Parse problems (an unclosed quote, a lone `-`, `--=`) are collected as errors instead of thrown.

### Console output (`src/Output`)

- `Console` writes colored text with ANSI 256-color codes. It has helpers such as `line()`, `success()`, `warning()`, `error()`, `info()`, `title()`, `debug()`, and `fatal()`. `debug()` prints only when the environment is `development`.
- `Theme` holds a named color palette and eight built-in themes: `default`, `monochromatic`, `high-contrast`, `pastel`, `amber`, `forest`, `ocean`, and `sunset`. You can register your own colors and themes.
- `ExtensionRegistry` and `ExtensionInterface` are a first pass at letting other code add new methods to `Console`. No extensions exist yet.

### Commands

- `Command` is an abstract base class. A command sets `$name` and `$group`, describes its arguments and options in `configure()`, and does its work in `execute()`.
- `CommandDefinition` is a fluent builder for those arguments, options, and the description.
- `CommandLocator` finds every `*Command.php` file in a set of folders and creates the command classes it finds.
- `CommandRegistry` stores commands by name.
- `App` ties it together. It prints a header, finds the command, and runs it.
- `Commands/ListCommand` lists the known commands by group.

### Environment

`EnvLoader` reads a `.env` file from the current folder into `$_ENV`. The CLI uses two values:

- `CI_CONSOLE_THEME`: the theme name (default: `default`)
- `CI_ENVIRONMENT`: `development` or `production` (default: `production`)

The `env` file in the repo root is a sample.

### Entry point

`bin/spark` loads Composer's autoloader and the `.env` file. Then it looks for commands in `app/Commands`, `src/Commands`, and `Commands` under the current folder, and runs the app.

## Current state

The input parser and the output/theme code work and have tests. The command side does not work end to end yet. Known problems:

- **Commands never run.** `App::run()` calls `$cmd->handle($input)`. `Command` has no `handle()` method. `Command::__call()` silently swallows the call. So `bin/spark list` prints the header and nothing else.
- **`bin/spark` and `bin/spark help` crash.** `ListCommand` calls `Console::clearScreen()`. That method calls `fwrite()`, `newLine()`, and `streamSupports()` as if they were methods on the class, and they are not. PHP then throws "Typed property ... `$extensionRegistry` must not be accessed before initialization."
- **Argument checks are wrong.** `Command::validateInput()` uses the error count as the argument index. It only works for the first required argument.
- **`Command::showHelp()` is empty.**
- **`composer.json` points to the wrong binary.** It lists `bin/ci`, but the file is `bin/spark`.
- **No PHAR build.** The `composer.json` description mentions building PHARs. Nothing in the repo does that yet. `bin/spark` only has a code path that would load an autoloader from inside a PHAR.
- **Commands are only found from the right folder.** `ListCommand` lives in `src/Commands`. `bin/spark` finds it only when you run it from this repo's root.
- **One test fails.** In `tests/Output/ConsoleTest.php`, the test at line 308 expects `title()` to print `===`. `title()` does not print that.

## Running the tests

You need PHP 8.4 or newer.

```bash
composer install
vendor/bin/pest
```

At the time of writing, 196 tests pass and 1 fails (see above).

## License

MIT. See [LICENSE](LICENSE).
