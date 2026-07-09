<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VerifySessionFingerprint
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $session = $request->session();
            
            // Build fingerprint from User-Agent and first 3 segments of IP (subnet)
            // Using subnet avoids logging out users whose IP changes slightly due to mobile network roaming
            $fingerprint = md5($request->userAgent() . '|' . $this->getIpSubnet($request->ip()));

            if (!$session->has('session_fingerprint')) {
                $session->put('session_fingerprint', $fingerprint);
            } elseif ($session->get('session_fingerprint') !== $fingerprint) {
                // Terminate session due to change in browser/device fingerprint
                Auth::logout();
                
                $session->invalidate();
                $session->regenerateToken();

                return redirect()->route('login')->withErrors([
                    'email' => 'Session terminated due to device or network change.',
                ]);
            }
        }

        return $next($request);
    }

    /**
     * Get the IP subnet (first 3 octets for IPv4, first 3 blocks for IPv6)
     */
    protected function getIpSubnet(?string $ip): string
    {
        if (!$ip) {
            return '';
        }

        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $parts = explode('.', $ip);
            return isset($parts[0], $parts[1], $parts[2]) ? $parts[0] . '.' . $parts[1] . '.' . $parts[2] : $ip;
        }

        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            $parts = explode(':', $ip);
            return isset($parts[0], $parts[1], $parts[2]) ? $parts[0] . ':' . $parts[1] . ':' . $parts[2] : $ip;
        }

        return $ip;
    }
}
