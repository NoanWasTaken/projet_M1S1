<?php

namespace App\Entity;

use App\Repository\SiteSettingsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SiteSettingsRepository::class)]
#[ORM\Table(name: 'site_settings')]
class SiteSettings
{
    #[ORM\Id]
    #[ORM\Column(length: 100)]
    private string $settingKey;

    #[ORM\Column(length: 255)]
    private string $settingValue;

    public function __construct(string $key, string $value)
    {
        $this->settingKey   = $key;
        $this->settingValue = $value;
    }

    public function getSettingKey(): string
    {
        return $this->settingKey;
    }

    public function getSettingValue(): string
    {
        return $this->settingValue;
    }

    public function setSettingValue(string $value): static
    {
        $this->settingValue = $value;

        return $this;
    }
}
