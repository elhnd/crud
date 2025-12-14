<?php

namespace App\Enum;

enum QuestionType: string
{
    case SINGLE_CHOICE = 'single_choice';
    case MULTIPLE_CHOICE = 'multiple_choice';
    case TRUE_FALSE = 'true_false';

    public function getLabel(): string
    {
        return match ($this) {
            self::SINGLE_CHOICE => 'Single Choice',
            self::MULTIPLE_CHOICE => 'Multiple Choice',
            self::TRUE_FALSE => 'True / False',
        };
    }

    public function getDescription(): string
    {
        return match ($this) {
            self::SINGLE_CHOICE => 'Select one correct answer',
            self::MULTIPLE_CHOICE => 'Select all correct answers',
            self::TRUE_FALSE => 'Is this statement true or false?',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::SINGLE_CHOICE => 'fa-circle-dot',
            self::MULTIPLE_CHOICE => 'fa-square-check',
            self::TRUE_FALSE => 'fa-toggle-on',
        };
    }
}
