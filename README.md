# Palantir Demo App

This chat application makes use of asymmetric cryptographic keys for encrypting and decrypting messages. 
This ensures that the sender of the message is verified during decryption. 
Each chatroom creates unique key pairs and are downloadable as QR code images.
Instead of the usual login, this room key is used to prove identity as well as read and send messages.

**Features**

- Built with Symfony 5.3, PHP 8, and PostgreSQL
- Uses Facebook OAuth2 login for creation of rooms
- Uses 2FA during user login, set to 30 seconds, 6 digits for compatibility with Google and other TOTP authenticator apps
- Message Queues for asynchronous encryption of messages, using Symfony workers
- Encryption using Sodium

This demo source code is a subset of the original source code.
