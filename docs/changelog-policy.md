# Changelog Policy

The project uses a curated [CHANGELOG.md](../CHANGELOG.md) so release notes stay readable in both GitHub Releases and the in-app Update Center.

## Goal

Each release should answer these questions quickly:

- What changed for administrators, teachers, or participants?
- Is there anything new to configure or deploy?
- Is there anything security-relevant?
- Are there any manual upgrade steps?

## Structure

Keep releases in this format:

```md
## [v1.2.0] - 2026-04-19

### Added
- ...

### Changed
- ...

### Fixed
- ...

### Security
- ...

### Upgrade Notes
- ...
```

You do not need to use every section in every release, but the headings should stay consistent when possible.

## Writing Rules

- Write for operators and users, not for commit archaeology.
- One bullet should usually express one meaningful outcome.
- Prefer "what changed" and "why it matters".
- Avoid commit hashes, branch names, and overly low-level refactor notes.
- Call out migrations, cache resets, schema dumps, or required environment changes in `Upgrade Notes`.

## Release Flow

Before tagging a new release:

1. Move the relevant bullets from `## Unreleased` into a new tagged section.
2. Keep the tag format aligned with the Git tag, for example `v1.2.0`.
3. Add the release date in ISO-like format: `YYYY-MM-DD`.
4. Leave a fresh `## Unreleased` section at the top for future work.

For a ready-made draft block, use [docs/unreleased-template.md](unreleased-template.md).

## Relationship With GitHub Releases

The release workflow reads `CHANGELOG.md` and tries to extract the section matching the pushed Git tag.

- If a matching section is found, that text becomes the GitHub Release body.
- If no matching section is found, the workflow falls back to a simple generic release note.

Because the Update Center reads the latest GitHub Release body, well-structured changelog entries directly improve the in-app changelog display.

## Private Repository Note

If the canonical GitHub repository is private, the current Update Center cannot fetch latest release metadata anonymously from GitHub.

In that temporary setup:

- keep maintaining `CHANGELOG.md` as normal
- keep publishing Releases if useful for internal testing
- expect the in-app release check to become fully useful once the repository is public or once a separate update manifest or authenticated bridge is added
