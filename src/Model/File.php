<?php

declare(strict_types=1);

namespace RezoZero\Xilofone\Composer\Model;

final class File
{
    private string $iri;
    private string $name;
    private Project $project;

    public static function fromArray(array $data): self
    {
        $file = new self();
        $file->iri = $data['@id'] ?? '';
        $file->name = $data['name'] ?? '';
        $file->project = Project::fromArray($data['project'] ?? []);

        return $file;
    }

    public function getIri(): string
    {
        return $this->iri;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getProject(): Project
    {
        return $this->project;
    }
}
