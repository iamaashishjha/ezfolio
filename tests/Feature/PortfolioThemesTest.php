<?php

namespace Tests\Feature;

use App\Helpers\CoreConstants;
use Tests\TestCase;

class PortfolioThemesTest extends TestCase
{
    public function test_registered_portfolio_themes_have_views_and_assets(): void
    {
        foreach (CoreConstants::PORTFOLIO_THEMES as $theme) {
            $this->assertTrue(view()->exists("frontend.theme.{$theme}"), "Missing {$theme} view");
            $this->assertFileExists(public_path("assets/themes/{$theme}/css/styles.css"));
            $this->assertFileExists(public_path("assets/themes/{$theme}/css/custom.css"));
            $script = $theme === 'vega' ? 'scripts.js' : 'main.js';
            $this->assertFileExists(public_path("assets/themes/{$theme}/js/{$script}"));
        }
    }
}
