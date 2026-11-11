# Required protection for main

This file records the intended server-side settings for the repository's main branch. These settings must be enabled in GitHub repository settings; this file alone does not enforce them.

## Target

Create an active branch ruleset or branch protection rule matching main. Apply it to administrators as well as contributors.

## Required rules

- Require changes to arrive through a pull request.
- Require at least one approval before merge.
- Dismiss stale approvals when new commits are pushed.
- Require approval of the most recent reviewable push.
- Require all review conversations to be resolved.
- Block force pushes and branch deletion.
- Prevent direct pushes to main. Only the team leader should complete pull request merges.
- Use squash merging only, with the pull request title as the squash commit subject.

The CODEOWNERS file requests @StElliott95 for review. A teammate-authored pull request should be reviewed and merged by the team leader. A pull request authored by the team leader needs an independent teammate approval before the team leader merges it.
