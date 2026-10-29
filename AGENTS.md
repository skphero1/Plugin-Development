# Project delivery

The user wants all plugin source changes and installable ZIPs uploaded to the existing GitHub repository, skphero1/Plugin-Development, so versions are easy to review and download. This is standing authorization to push this project's completed updates and version tags; do not force-push or overwrite existing tags.

For each release, update the plugin header, asset versions, and package version together, run the appropriate checks, run `python dev/package.py`, commit source and generated `dist` artifacts, and push to GitHub. Keep the latest ZIP and a versioned ZIP in `dist`, and tag releases as `vVERSION`. Explain any validation that could not run. Never commit credentials, local databases, node_modules, or environment-specific files. Use the existing checkout; do not create worktrees unless requested. Publishing the cloud environment is separate and requires user instruction.
