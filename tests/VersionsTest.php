<?php

use PHPUnit\Framework\TestCase;

class VersionsTest extends TestCase
{
    /**
     * @dataProvider provideBranches
     */
    public function testBranches(
        array $expected,
        string $defaultBranch,
        string $minimumCmsMajor,
        string $composerJson = '',
        string $branchesJson = '',
        string $tagsJson = ''
    ) {
        $actual = branches($defaultBranch, $minimumCmsMajor, $composerJson, $branchesJson, $tagsJson);
        $this->assertSame($expected, $actual);
    }

    public function provideBranches()
    {
        return [
            'Standard' => [
                'expected' => ['4.13', '4', '5.0', '5'],
                'defaultBranch' => '5',
                'minimumCmsMajor' => '4',
                'composerJson' => <<<EOT
                {
                    "require": {
                        "silverstripe/framework": "^5.0"
                    }
                }
                EOT,
                'branchesJson' => <<<EOT
                [
                    {"name": "3"},
                    {"name": "3.6"},
                    {"name": "3.7"},
                    {"name": "4"},
                    {"name": "4.12"},
                    {"name": "4.13"},
                    {"name": "5"},
                    {"name": "5.0"},
                    {"name": "5.1"}
                ]
                EOT,
                'tagsJson' => <<<EOT
                [
                    {"name": "5.1.0-beta1"},
                    {"name": "5.0.9"},
                    {"name": "4.13.11"},
                    {"name": "3.7.4"}
                ]
                EOT,
            ],
            'Missing `1` branch and no silverstripe/framework in composer.json' => [
                'expected' => ['1.13', '2.0', '2'],
                'defaultBranch' => '2',
                'minimumCmsMajor' => '4',
                'composerJson' => <<<EOT
                {
                    "require": {
                        "php": "^8.1"
                    }
                }
                EOT,
                'branchesJson' => <<<EOT
                [
                    {"name": "1.12"},
                    {"name": "1.13"},
                    {"name": "2"},
                    {"name": "2.0"},
                    {"name": "2.1"}
                ]
                EOT,
                'tagsJson' => <<<EOT
                [
                    {"name": "2.1.0-beta1"},
                    {"name": "2.0.9"},
                    {"name": "1.13.11"}
                ]
                EOT,
            ],
        ];
    }
}