<?php

namespace App\Services;

class EmailValidationService
{
    /**
     * Validate email address format
     */
    public function validateEmail(string $email): bool
    {
        // Basic format check
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }
        
        // Check for common typos
        if ($this->hasCommonTypos($email)) {
            return false;
        }
        
        // Check domain has MX record (optional - can be slow)
        // Uncomment if needed:
        // return $this->hasMxRecord($email);
        
        return true;
    }
    
    /**
     * Validate and filter array of emails
     */
    public function validateBulk(array $emails): array
    {
        $valid = [];
        $invalid = [];
        
        foreach ($emails as $email) {
            $email = trim(strtolower($email));
            
            if ($this->validateEmail($email)) {
                $valid[] = $email;
            } else {
                $invalid[] = $email;
            }
        }
        
        return [
            'valid' => $valid,
            'invalid' => $invalid,
        ];
    }
    
    /**
     * Check for common email typos
     */
    private function hasCommonTypos(string $email): bool
    {
        $typos = [
            'gmial.com' => 'gmail.com',
            'gmai.com' => 'gmail.com',
            'yahooo.com' => 'yahoo.com',
            'yaho.com' => 'yahoo.com',
            'hotmial.com' => 'hotmail.com',
            'outlok.com' => 'outlook.com',
        ];
        
        foreach ($typos as $typo => $correct) {
            if (str_contains($email, $typo)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Check if domain has MX record
     */
    private function hasMxRecord(string $email): bool
    {
        $domain = substr(strrchr($email, '@'), 1);
        
        if (!$domain) {
            return false;
        }
        
        return checkdnsrr($domain, 'MX');
    }
    
    /**
     * Get validation summary
     */
    public function getSummary(array $result): string
    {
        $total = count($result['valid']) + count($result['invalid']);
        $validCount = count($result['valid']);
        $invalidCount = count($result['invalid']);
        $validPercent = $total > 0 ? round(($validCount / $total) * 100, 1) : 0;
        
        return "Validated {$total} emails: {$validCount} valid ({$validPercent}%), {$invalidCount} invalid";
    }
}
