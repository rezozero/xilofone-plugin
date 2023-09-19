<?php

declare(strict_types=1);

namespace RezoZero\Xilofone\Composer\Model;

final class Project
{
    private string $iri;
    private string $name;
    private array $locales;
    public static function fromArray(array $data): self
    {
        $project = new self();
        $project->iri = $data['@id'] ?? '';
        $project->name = $data['name'] ?? '';
        $project->locales = $data['locales'] ?? [];
        return $project;
    }

    public function getIri(): string
    {
        return $this->iri;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getLocales(): array
    {
        return $this->locales;
    }
}
