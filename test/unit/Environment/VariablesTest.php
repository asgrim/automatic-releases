<?php

declare(strict_types=1);

namespace Doctrine\AutomaticReleases\Test\Unit\Environment;

use Doctrine\AutomaticReleases\Environment\Variables;
use PHPUnit\Framework\TestCase;
use function Safe\putenv;
use function uniqid;

final class VariablesTest extends TestCase
{
    /** @runInSeparateProcess */
    public function testReadsEnvironmentVariables() : void
    {
        $githubHookSecret   = uniqid('githubHookSecret', true);
        $signingSecretKey   = uniqid('signingSecretKey', true);
        $githubToken        = uniqid('githubToken', true);
        $githubOrganisation = uniqid('githubOrganisation', true);

        putenv('GITHUB_HOOK_SECRET=' . $githubHookSecret);
        putenv('GITHUB_TOKEN=' . $githubToken);
        putenv('SIGNING_SECRET_KEY=' . $signingSecretKey);
        putenv('GITHUB_ORGANISATION=' . $githubOrganisation);

        $variables = Variables::fromEnvironment();

        self::assertSame($githubHookSecret, $variables->githubHookSecret());
        self::assertSame($signingSecretKey, $variables->signingSecretKey());
        self::assertSame($githubToken, $variables->githubToken());
        self::assertSame($githubOrganisation, $variables->githubOrganisation());
    }
}
