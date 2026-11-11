# Contribution workflow

This is a university team project. Work is divided by the WEB2 project tasks and reviewed through GitHub pull requests.

## Branches

Do not commit or push work directly to main. Start each task from the latest main branch and use one branch per task.

- Feature task: task-<number>-<short-description>, for example task-2-database or task-7-api-integrations
- Bug fix: fix/<short-description>
- Documentation: docs/<short-description>
- Repository maintenance: chore/<short-description>

Before creating a branch:

    git fetch origin
    git switch main
    git pull --ff-only origin main
    git switch -c task-<number>-<short-description>

Do not force-push. If a published branch needs correction, add a follow-up commit. Do not rewrite shared history without agreement from the team leader.

## Commit messages

Use the Conventional Commits format:

    type(scope): imperative summary

Allowed types for this project are feat, fix, docs, refactor, test, chore, style, perf, and revert. Use a task number or area as the scope.

Examples:

- feat(task-2): add TMDB movie tables
- fix(auth): reject duplicate email addresses
- docs(repo): document the contribution workflow

Keep each commit to one logical change. Use a body to explain why when the change is not obvious. Avoid vague subjects such as update, changes, or final. The PR title must use the same format because squash merges use the PR title as the main-branch commit subject.

To use the provided local commit template:

    git config commit.template .github/commit-message-template.txt

## Pull requests

- Open one focused pull request for each task and target main.
- Include the task number, summary, affected files, and verification performed in the PR description.
- Request @StElliott95 to review contributor pull requests.
- Wait for approval and resolve review conversations before merge.
- The team leader reviews and merges teammate pull requests.
- If the team leader authors a pull request, a teammate must review it before the team leader merges it.
- Do not merge an incomplete or dependent pull request before its prerequisite.

For work that depends on another open pull request, either wait for the prerequisite to merge or create a stacked pull request targeting that prerequisite branch. State the dependency in the PR description. After the prerequisite is merged, retarget the stacked PR to main and update its branch before review and merge.

After merge, delete the task branch and update local main with a fast-forward pull.

## Main branch protection

The intended GitHub rules for main are listed in .github/MAIN_BRANCH_RULES.md. Until those server-side rules are active, follow this process manually and do not push directly to main.
