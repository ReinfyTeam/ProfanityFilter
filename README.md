# ProfanityFilter

ProfanityFilter filters configured profanity in PocketMine-MP chat and command
messages. It can block a message, hide matched words, and apply a moderation
action after repeated violations.

[![Build](https://github.com/ReinfyTeam/ProfanityFilter/actions/workflows/build.yml/badge.svg)](https://github.com/ReinfyTeam/ProfanityFilter/actions/workflows/build.yml)
[![Latest release](https://img.shields.io/github/v/release/ReinfyTeam/ProfanityFilter)](https://github.com/ReinfyTeam/ProfanityFilter/releases/latest)
[![License](https://img.shields.io/github/license/ReinfyTeam/ProfanityFilter)](LICENSE)

## Features

- Block a message or replace matched words with a configured character.
- Use the bundled profanity list or maintain a server-specific list.
- Manage custom words from the `/profanityfilter` command or its `/pf` alias.
- Open a PMForms management UI in-game.
- Count violations and kick, ban, or run a configured command at the limit.
- Configure command and bypass permissions.
- Use the English or Vietnamese language resources, or provide a custom locale.
- Keep the bundled word list immutable while allowing custom words in plugin data.

## Requirements

| Requirement | Supported scope |
| --- | --- |
| PocketMine-MP or Axolotl-PM | API `5.36.0` or newer within the API 5 line |
| PHP | `8.4` in the build workflow |
| Runtime dependency | The PHAR contains the required dependencies |

The repository build pins Axolotl-PM `5.51.1` for analysis and packaging. A
passing build does not certify every API 5 server revision or every Bedrock
client version.

## Installation

1. Download `ProfanityFilter.phar` from [GitHub Releases](https://github.com/ReinfyTeam/ProfanityFilter/releases) or the [Poggit project page](https://poggit.pmmp.io/p/ProfanityFilter).
2. Copy the PHAR to the server's `plugins/` directory.
3. Start the server once so it creates the data files.
4. Edit `plugin_data/ProfanityFilter/config.yml` and
   `plugin_data/ProfanityFilter/profanity.yml`.
5. Restart the server after changing configuration.

The plugin has no mandatory external runtime plugin. Its PHAR includes the
dependencies required by the active code path.

## Filtering modes

Set `type` in `config.yml` to one of these values:

| Mode | Behavior |
| --- | --- |
| `block` | Cancel the message and send `block-message` to the player |
| `hide` | Replace each matched word with `replacementCharacter` and keep the message flow |

Set `profanity` to `custom` to use the editable list in
`plugin_data/ProfanityFilter/profanity.yml`. Set it to `provided` to use the
bundled list. Custom words are managed by the plugin and can also be edited
manually while the server is stopped.

Matching is case-insensitive and can find a configured word inside a longer
word. The filter processes outgoing chat and command-related text after other
plugins may already have changed it. It cannot restore text removed by an
earlier plugin.

## Commands and permissions

The command permission is configurable through `command-permission` and is
`profanityfilter.command` by default. The bypass permission is configurable
through `bypass-permission` and is `profanityfilter.bypass` by default.

| Command | Purpose |
| --- | --- |
| `/profanityfilter help` | Show the localized command help |
| `/profanityfilter ui` | Open the management form in-game |
| `/profanityfilter info` | Show credits and plugin information |
| `/profanityfilter list` | List words from the active custom list |
| `/profanityfilter add <word>` | Add a word to the custom list |
| `/profanityfilter remove <word>` | Remove a word from the custom list |
| `/profanityfilter toggle` | Enable or disable filtering until reload |
| `/profanityfilter reload` | Reload plugin configuration and word data |
| `/pf <subcommand>` | Alias for `/profanityfilter` |

The UI subcommand is available only to a player. Console operators can use the
command subcommands that do not require a player form.

## Configuration

The main settings in `plugin_data/ProfanityFilter/config.yml` are:

| Key | Purpose |
| --- | --- |
| `type` | Select `block` or `hide` |
| `replacementCharacter` | Character used by `hide` mode |
| `block-message` | Message sent when a message is blocked |
| `lang` | Select `eng`, `vie`, or `custom` |
| `punishment-type` | Select `kick`, `ban`, or `command` |
| `max-violations` | Violation count needed for moderation |
| `ban-duration` | Duration used by native ban handling |
| `execute-as-player` | Run the custom punishment command as the player when true |
| `command` | Command used when `punishment-type` is `command` |
| `command-permission` | Permission for the management command |
| `bypass-permission` | Permission that skips filtering |
| `profanity` | Select `custom` or `provided` |
| `check-updates` | Enable or disable update checks |

The `config-version` value is managed by the plugin. Do not edit it manually.
When the default configuration changes, the loader keeps the old file and
creates the current resource version.

## Languages

The repository includes English and Vietnamese resources. To add a custom
language, set `lang: custom` and create a language file in
`plugin_data/ProfanityFilter/languages/`. Use an ISO 639-2 code when adding a
language that may be contributed back to the project.

## Build and verification

The `stable` branch workflow runs PHP linting through the build tools, PHPStan
at maximum level, the regression test, a PHAR build, and a packaged UI check.
The build also creates the Poggit package on pushes to `stable`.

Local checks use the locked dependencies:

```sh
composer install
composer analyze
composer test
composer build
```

The packaged UI test is separate from a live Bedrock client test. A green
source or PHAR check does not prove that every client version renders a label
or form the same way.

## License and attribution

See [LICENSE](LICENSE) for the repository's LGPL license text. The project
credits ReinfyTeam, NhanAZ and the other listed contributors in the source
history. Report bugs through [GitHub Issues](https://github.com/ReinfyTeam/ProfanityFilter/issues) and include the server version, plugin version and relevant logs.
