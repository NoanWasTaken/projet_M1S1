<?php

namespace App\Service;

use App\Entity\SiteSettings;
use App\Repository\SiteSettingsRepository;
use Doctrine\ORM\EntityManagerInterface;

class SiteSettingsService
{
    public function __construct(
        private SiteSettingsRepository $repository,
        private EntityManagerInterface $em,
    ) {}

    public function get(string $key, string $default = ''): string
    {
        return $this->repository->getValue($key, $default);
    }

    public function set(string $key, string $value): void
    {
        $setting = $this->repository->find($key);

        if ($setting === null) {
            $setting = new SiteSettings($key, $value);
            $this->em->persist($setting);
        } else {
            $setting->setSettingValue($value);
        }

        $this->em->flush();
    }

    public function isChatbotEnabled(): bool
    {
        return $this->get('chatbot_enabled', '1') === '1';
    }

    public function toggleChatbot(): bool
    {
        $enabled = $this->isChatbotEnabled();
        $this->set('chatbot_enabled', $enabled ? '0' : '1');

        return !$enabled;
    }
}
