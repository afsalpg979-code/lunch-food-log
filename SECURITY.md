# Security notes

- Keep `data/lunch.sqlite` outside the public web root when deploying to a public server, or deny direct access to the `data/` directory.
- Food entry updates and deletes should use POST requests.
- The app uses prepared SQL statements for database writes and queries.
- Validate and escape all user-provided data before storage/display.
- For a multi-user/public deployment, add authentication and CSRF protection to every state-changing endpoint.
