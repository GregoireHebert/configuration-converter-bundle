# Contributing to this bundle

First of all, thank you for contributing, you're awesome!

To have your code integrated, there is some rules to follow, but don't panic, it's easy!

## Reporting Bugs

If you happen to find a bug, we kindly request you to report it. However, before submitting it, please:

* Check if the bug is not already reported!
* A clear title to resume the issue
* A description of the workflow needed to reproduce the bug

> _NOTE:_ Don’t hesitate giving as much information as you can (OS, PHP version extensions...)

### Security issues

If you find a security issue, send a mail to Grégoire Hébert <gregoire@les-tilleuls.coop>. **Please do not report security problems
publicly**. We will disclose details of the issue and credit you after having released a new version including a fix.

## Pull requests

### Writing a Pull Request

First of all, you must decide on what branch your changes will be based depending of the nature of the change.

### Matching Coding Standards

The bundle follows [Symfony coding standards](https://symfony.com/doc/current/contributing/code/standards.html).
But don't worry, you can fix CS issues automatically using the [PHP CS Fixer](http://cs.sensiolabs.org/) tool

```bash
vendor/bin/php-cs-fixer fix
vendor/bin/phpstan -l7 analyze src tests
```

And then, add fixed file to your commit before push.
Be sure to add only **your modified files**. If another files are fixed by cs tools, just revert it before commit.

### Sending a Pull Request

When you send a PR, just make sure that:

* You add valid test cases.
* Tests are green.
* You make the PR on the same branch you based your changes on. If you see commits that you did not make in your PR, you're doing it wrong.
* Also don't forget to add a comment when you update a PR with a ping so I get a notification.
* Squash your commits into one commit. (see the next chapter)

All Pull Requests must include [this header](.github/PULL_REQUEST_TEMPLATE.md).

### Tests

#### Phpunit and Coverage Generation

To launch unit tests:

```
vendor/bin/phpunit --stop-on-failure -vvv
```

If you want coverage, you will need the `phpdbg` package or `xdebug` and run:

```
phpdbg -qrr vendor/bin/phpunit --coverage-html dist -vvv --stop-on-failure
vendor/bin/phpunit --coverage-html dist -vvv --stop-on-failure
```

Sometimes there might be an error with too many open files when generating coverage. To fix this, you can increase the `ulimit`, for example:

```
ulimit -n 4000
```

Coverage will be available in `dist/index.html`.

## Squash your Commits

If you have 3 commits. So start with:

```bash
git rebase -i HEAD~3
```

An editor will be opened with your 3 commits, all prefixed by `pick`.

Replace all `pick` prefixes by `fixup` (or `f`) **except the first commit** of the list.

Save and quit the editor.

After that, all your commits where squashed into the first one and the commit message of the first commit.

If you would like to rename your commit message type:

```bash
git commit --amend
```

Now force push to update your PR:

```bash
git push --force
```

# License and Copyright Attribution

When you open a Pull Request to this bundle, you agree to license your code under the [MIT license](LICENSE)
and to transfer the copyright on the submitted code to Grégoire Hébert.

Be sure to you have the right to do that (if you are a professional, ask your company)!

If you include code from another project, please mention it in the Pull Request description and credit the original author.
