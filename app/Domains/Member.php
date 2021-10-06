<?php


namespace App\Domains;

class Member
{
    const MEMBER_VK         = 'vk';
    const MEMBER_CORE       = 'core';

    const ALLOWED_MEMBERS = [
        self::MEMBER_VK,
        self::MEMBER_CORE,
    ];

    /** @var string  */
    private $owner;

    /** @var string */
    private $owner_id;

    public function __construct(string $owner)
    {
        throw_if(!in_array($owner, self::ALLOWED_MEMBERS), \Exception::class);
        $this->owner = $owner;
    }

    public function setOwnerId(string $owner_id): self {
        $this->owner_id = $owner_id;
        return $this;
    }

    public function getOwner(): string {
        return $this->owner;
    }

    public function getOwnerId(): string {
        return $this->owner_id;
    }

    /** @param string[] $member */
    public static function fromArray($member): self {
        return (new self($member['owner']))->setOwnerId($member['owner_id']);
    }

    /** @return string[] */
    public function toArray(): array {
        return ['owner' => $this->getOwner(), 'owner_id' => $this->getOwnerId()];
    }

    public function toString(): string {
        return "{$this->getOwner()}|{$this->getOwnerId()}";
    }

    public static function fromString(string $member_string): Member {
        [$owner, $owner_id] = explode('|', $member_string);
        return (new self($owner))->setOwnerId($owner_id);
    }
}
