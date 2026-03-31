# RakLib IPC

This project provides utilities and protocols for interacting with [RakLib](https://github.com/pmmp/RakLib) via message-based channels.

The library defines the following interfaces which must be implemented:
- `InterThreadChannelReader`
- `InterThreadChannelWriter`

The method of transmitting messages is up to you - it could use sockets, pthreads `Threaded` objects, parallel `Channel`, or anything else.
