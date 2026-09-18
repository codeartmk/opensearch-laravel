<?php

namespace Codeart\OpensearchLaravel\Aggregations\Types;

use Codeart\OpensearchLaravel\Interfaces\OpenSearchQuery;

/**
 * A `geohash_grid` bucket aggregation: buckets geo points into the geohash cells they fall in.
 *
 * @see https://opensearch.org/docs/latest/aggregations/bucket/geohash-grid/
 */
class GeohashGrid implements OpenSearchQuery, AggregationType
{
    /**
     * @param string $field The geo_point field
     * @param int|null $precision The geohash length, 1 to 12; longer means smaller cells. Null leaves it out so
     *                            OpenSearch's default (5) applies
     * @param int|null $size The maximum number of buckets returned. Null leaves it out so OpenSearch's default (10000)
     *                       applies
     */
    public function __construct(
        private readonly string $field,
        private readonly ?int $precision,
        private readonly ?int $size
    ){}

    /**
     * @param string $field The geo_point field
     * @param int|null $precision The geohash length, 1 to 12; longer means smaller cells. Null leaves it out so
     *                            OpenSearch's default (5) applies
     * @param int|null $size The maximum number of buckets returned. Null leaves it out so OpenSearch's default (10000)
     *                       applies
     * @return self
     */
    public static function make(string $field, ?int $precision = null, ?int $size = null): self
    {
        return new self($field, $precision, $size);
    }

    /**
     * @return array{geohash_grid: array{field: string, precision?: int, size?: int}}
     */
    public function toOpenSearchQuery(): array
    {
        $query = [
            'geohash_grid' => [
                'field' => $this->field,
            ]
        ];

        if (!is_null($this->precision)) {
            $query['geohash_grid']['precision'] = $this->precision;
        }

        if (!is_null($this->size)) {
            $query['geohash_grid']['size'] = $this->size;
        }

        return $query;
    }
}
