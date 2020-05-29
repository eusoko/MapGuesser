<?php namespace MapGuesser\Http;

class Request
{
    const HTTP_GET = 0;

    const HTTP_POST = 1;

    private string $url;

    private int $method;

    private string $query = '';

    private array $headers = [];

    public function __construct(string $url, int $method = self::HTTP_GET)
    {
        $this->url = $url;
        $this->method = $method;
    }

    public function setQuery($query)
    {
        if (is_string($query)) {
            $this->query = $query;
        } else {
            $this->query = http_build_query($query);
        }
    }

    public function setHeaders(array $headers)
    {
        $this->headers = array_merge($this->headers, $headers);
    }

    public function send(): Response
    {
        $ch = curl_init();

        if ($this->method === self::HTTP_GET) {
            $url = $this->url . '?' . $this->query;
        } elseif ($this->method === self::HTTP_POST) {
            $url  = $this->url;

            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $this->query);
        }

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, 'MapGuesser cURL/1.0');

        if (count($this->headers) > 0) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers);
        }

        $responseHeaders = [];
        curl_setopt(
            $ch,
            CURLOPT_HEADERFUNCTION,
            function ($ch, $header) use (&$responseHeaders) {
                $len = strlen($header);
                $header = explode(':', $header, 2);

                if (count($header) < 2) {
                    return $len;
                }

                $responseHeaders[strtolower(trim($header[0]))][] = trim($header[1]);

                return $len;
            }
        );

        $responseBody = curl_exec($ch);

        if ($responseBody === false) {
            $error = curl_error($ch);

            curl_close($ch);

            throw new \Exception($error);
        }

        curl_close($ch);

        return new Response($responseBody, $responseHeaders);
    }
}
