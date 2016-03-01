# Change Log

All Notable changes to `telnet-client` will be documented in this file

## v2.0.0 - 2016-03-01

### Added
- Unified exceptions now thrown, all implement `TelnetExceptionInterface`
- `TelnetClient::factory` to replace `TelnetClientBuilder`
- More tests

### Changed
- Don't connect on `__construct` but provide a `connect` method. This allows the client to be injected as a dependency without requiring the dsn to be known ahead of time.

### Fixed
- Added missing `TelnetClient::getSocket` method

### Deprecated
- Nothing

### Removed
- `TelnetClientBuilder`

### Security
- Nothing


## v1.0.0 - 2016-02-16

### Added
- Everything

### Changed
- Everything

### Fixed
- Nothing

### Deprecated
- Nothing

### Removed
- Nothing

### Security
- Nothing
