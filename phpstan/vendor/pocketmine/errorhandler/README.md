# ErrorHandler
![CI](https://github.com/pmmp/ErrorHandler/workflows/CI/badge.svg?branch=master)

This library contains some small utilities intended to make PHP's E_* errors more bearable.

- `ErrorToExceptionHandler` contains a basic error handler used to convert E_* errors into thrown `\ErrorException`s.
- `ErrorTypeToStringMap` contains a utility to convert E_* codes into human-readable text.
