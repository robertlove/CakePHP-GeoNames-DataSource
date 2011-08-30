<?php
/**
 * GeoNames Source
 */
class GeoNamesSource extends DataSource
{
    /**
     * Query
     *
     * @param string $name The name of the method being called.
     * @param array $arguments The arguments to pass to the method.
     * @return mixed A result array if successful, false otherwise.
     */
    public function query($name = null, $arguments = array())
    {
        $arguments = isset($arguments[0]) ? $arguments[0] : $arguments;
        $query = array_merge(array(
            'username' => $this->config['username'],
        ), $arguments);
        if ($this->config['cache'] === true) {
            $cacheKey = md5(serialize($query));
            if ($results = Cache::read($cacheKey)) {
                return $results;
            }
        }
        $url = 'http://api.geonames.org/' . $name . 'JSON?' . http_build_query($query);
        try {
            if ($response = file_get_contents($url)) {
                if ($results = json_decode($response, true)) {
                    if ((isset($results['status']['message'])) && (isset($results['status']['value']))) {
                        throw new CakeException($results['status']['message'], $results['status']['value']);
                    } else {
                        if ($this->config['cache'] === true) {
                            Cache::write($cacheKey, $results);
                        }
                        return $results;
                    }
                }
            }
        } catch (CakeException $e) {
            echo $e->getMessage() . "\n";
        }
        return false;
    }
}