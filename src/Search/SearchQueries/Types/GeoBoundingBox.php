<?php

namespace Codeart\OpensearchLaravel\Search\SearchQueries\Types;

use Codeart\OpensearchLaravel\Interfaces\OpenSearchQuery;

/**
 * A `geo_bounding_box` query: matches documents whose geopoint lies inside the rectangle.
 *
 * @see https://opensearch.org/docs/latest/query-dsl/geo-and-xy/geo-bounding-box/
 */
class GeoBoundingBox implements SearchQueryType, OpenSearchQuery
{
    /**
     * @param string $field The geo_point field
     * @param array<array-key, int|float>|string $topLeft The top-left corner in any geopoint format, e.g. ['lat' =>
     *                                                    42.1, 'lon' => 20.4] or a geohash
     * @param array<array-key, int|float>|string $bottomRight The bottom-right corner in any geopoint format, e.g.
     *                                                        ['lat' => 40.8, 'lon' => 23.1] or a geohash
     */
    public function __construct(
        private readonly string $field,
        private readonly array|string $topLeft,
        private readonly array|string $bottomRight
    ){}

    /**
     * @param string $field The geo_point field
     * @param array<array-key, int|float>|string $topLeft The top-left corner in any geopoint format, e.g. ['lat' =>
     *                                                    42.1, 'lon' => 20.4] or a geohash
     * @param array<array-key, int|float>|string $bottomRight The bottom-right corner in any geopoint format, e.g.
     *                                                        ['lat' => 40.8, 'lon' => 23.1] or a geohash
     * @return self
     */
    public static function make(string $field, array|string $topLeft, array|string $bottomRight): self
    {
        return new self($field, $topLeft, $bottomRight);
    }

    /**
     * @return array{geo_bounding_box: array<string, array{top_left: array<array-key, int|float>|string, bottom_right: array<array-key, int|float>|string}>}
     */
    public function toOpenSearchQuery(): array
    {
        return [
            'geo_bounding_box' => [
                $this->field => [
                    'top_left' => $this->topLeft,
                    'bottom_right' => $this->bottomRight
                ]
            ]
        ];
    }
}
