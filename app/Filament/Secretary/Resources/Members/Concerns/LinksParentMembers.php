<?php

namespace App\Filament\Secretary\Resources\Members\Concerns;

use App\Models\Member;

trait LinksParentMembers
{
    /**
     * @var array<string, float>
     */
    public array $parentMemberLinkedAt = [];

    public function linkParentMember(string $nameField, string $idField, int $memberId): void
    {
        $male = $this->parentMemberIsMale($nameField, $idField);

        if ($male === null) {
            return;
        }

        $member = Member::query()
            ->whereKey($memberId)
            ->where('sex', $male)
            ->first();

        if ($member === null) {
            return;
        }

        $this->parentMemberLinkedAt[$nameField] = microtime(true);
        $this->setParentMemberState($nameField, $member->name);
        $this->setParentMemberState($idField, $member->id);
    }

    public function parentLinkIsFresh(string $nameField): bool
    {
        $linkedAt = $this->parentMemberLinkedAt[$nameField] ?? null;

        return is_float($linkedAt) && (microtime(true) - $linkedAt) < 2;
    }

    protected function parentMemberIsMale(string $nameField, string $idField): ?bool
    {
        return match ($nameField.'|'.$idField) {
            'father_name|father_member_id' => true,
            'mother_name|mother_member_id' => false,
            default => null,
        };
    }

    protected function setParentMemberState(string $field, mixed $value): void
    {
        if (filled($this->mountedActions ?? null)) {
            $index = array_key_last($this->mountedActions);
            $this->mountedActions[$index]['data'][$field] = $value;

            return;
        }

        $this->data[$field] = $value;
    }
}
