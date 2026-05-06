<?php

namespace App\Services;

use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Token\Plain;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Lcobucci\JWT\Validation\Constraint\StrictValidAt;
use DateTimeImmutable;
use App\Models\User;

class JwtService
{
    private Configuration $config;
    private string $issuer;
    private string $audience;

    public function __construct()
    {
        $key = InMemory::plainText(config('app.key'));
        $this->config = Configuration::forSymmetricSigner(new Sha256(), $key);
        $this->issuer = config('app.url');
        $this->audience = config('app.url');
    }

    /**
     * Generate JWT token for car inspector
     */
    public function generateToken(User $user): string
    {
        $now = new DateTimeImmutable();
        $expiresAt = $now->modify('+24 hours');

        $token = $this->config->builder()
            ->issuedBy($this->issuer)
            ->permittedFor($this->audience)
            ->identifiedBy(uniqid(), true)
            ->issuedAt($now)
            ->canOnlyBeUsedAfter($now)
            ->expiresAt($expiresAt)
            ->withClaim('user_id', $user->id)
            ->withClaim('user_type', $user->user_type)
            ->withClaim('email', $user->email)
            ->getToken($this->config->signer(), $this->config->signingKey());

        return $token->toString();
    }

    /**
     * Generate refresh token (longer expiration)
     */
    public function generateRefreshToken(User $user): string
    {
        $now = new DateTimeImmutable();
        $expiresAt = $now->modify('+7 days');

        $token = $this->config->builder()
            ->issuedBy($this->issuer)
            ->permittedFor($this->audience)
            ->identifiedBy(uniqid(), true)
            ->issuedAt($now)
            ->canOnlyBeUsedAfter($now)
            ->expiresAt($expiresAt)
            ->withClaim('user_id', $user->id)
            ->withClaim('user_type', $user->user_type)
            ->withClaim('email', $user->email)
            ->withClaim('type', 'refresh')
            ->getToken($this->config->signer(), $this->config->signingKey());

        return $token->toString();
    }

    /**
     * Validate and parse JWT token.
     *
     * lcobucci/jwt v5 removed the separate lcobucci/clock package, so
     * \Lcobucci\Clock\SystemClock no longer exists. StrictValidAt now accepts
     * any PSR-20 ClockInterface. We provide a minimal inline implementation
     * that returns the current system time — identical in behaviour to the old
     * SystemClock but with no external package dependency.
     */
    public function validateToken(string $tokenString): ?Plain
    {
        try {
            $token = $this->config->parser()->parse($tokenString);

            if (!$token instanceof Plain) {
                return null;
            }

            // PSR-20 clock replacing the removed \Lcobucci\Clock\SystemClock (lcobucci/jwt v5+)
            $systemClock = new class implements \Psr\Clock\ClockInterface {
                public function now(): \DateTimeImmutable
                {
                    return new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
                }
            };

            $constraints = [
                new SignedWith($this->config->signer(), $this->config->signingKey()),
                new StrictValidAt($systemClock),
            ];

            if (!$this->config->validator()->validate($token, ...$constraints)) {
                return null;
            }

            return $token;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get user from token
     */
    public function getUserFromToken(string $tokenString): ?User
    {
        $token = $this->validateToken($tokenString);

        if (!$token) {
            return null;
        }

        $userId  = $token->claims()->get('user_id');
        $userType = $token->claims()->get('user_type');

        if ($userType !== 'car_inspector') {
            return null;
        }

        return User::with('carInspector')->find($userId);
    }

    /**
     * Check if token is expired
     */
    public function isTokenExpired(string $tokenString): bool
    {
        return $this->validateToken($tokenString) === null;
    }

    /**
     * Extract claims from token without validation (for debugging)
     */
    public function getTokenClaims(string $tokenString): ?array
    {
        try {
            $token = $this->config->parser()->parse($tokenString);

            if (!$token instanceof Plain) {
                return null;
            }

            return $token->claims()->all();
        } catch (\Exception $e) {
            return null;
        }
    }
}