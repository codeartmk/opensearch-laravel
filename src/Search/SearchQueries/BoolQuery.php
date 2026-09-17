<?php

namespace Codeart\OpensearchLaravel\Search\SearchQueries;

use Codeart\OpensearchLaravel\Exceptions\InvalidSearchParametersException;
use Codeart\OpensearchLaravel\Interfaces\OpenSearchQuery;

class BoolQuery implements OpenSearchQuery
{
    private const CLAUSES = [Must::class, Should::class, MustNot::class, Filter::class];

    private const OPTIONS = ['minimum_should_match', 'boost'];

    /**
     * @param array $parameters Must, Should, MustNot and Filter clauses, plus the optional minimum_should_match and boost keys
     * @throws InvalidSearchParametersException
     */
    public function __construct(
        private readonly array $parameters
    ) {
        $clauses = [];

        foreach ($parameters as $key => $parameter) {
            $clause = self::clauseOf($parameter);

            if (!is_null($clause)) {
                if (isset($clauses[$clause])) {
                    throw new InvalidSearchParametersException(sprintf(
                        'BoolQuery accepts only one %s clause.',
                        class_basename($clause)
                    ));
                }

                $clauses[$clause] = true;

                continue;
            }

            if (is_string($key) && in_array($key, self::OPTIONS, true)) {
                continue;
            }

            if (is_string($key)) {
                throw new InvalidSearchParametersException(
                    "BoolQuery accepts only the minimum_should_match and boost options, \"$key\" given."
                );
            }

            throw new InvalidSearchParametersException(sprintf(
                'BoolQuery accepts only Must, Should, MustNot and Filter clauses, %s given.',
                get_debug_type($parameter)
            ));
        }
    }

    /**
     * @throws InvalidSearchParametersException
     */
    public static function make(array $parameters): self
    {
        return new self($parameters);
    }

    /**
     * Returns the clause class the parameter is an instance of, or null when it isn't a clause.
     */
    private static function clauseOf(mixed $parameter): ?string
    {
        foreach (self::CLAUSES as $clause) {
            if ($parameter instanceof $clause) {
                return $clause;
            }
        }

        return null;
    }

    public function toOpenSearchQuery(): array
    {
        $resulting = [
            'bool' => []
        ];

        foreach ($this->parameters as $parameter) {
            if($parameter instanceof Must) {
                $resulting['bool']['must'] = $parameter->toOpenSearchQuery();
            }

            if($parameter instanceof Should) {
                $resulting['bool']['should'] = $parameter->toOpenSearchQuery();
            }

            if($parameter instanceof MustNot) {
                $resulting['bool']['must_not'] = $parameter->toOpenSearchQuery();
            }

            if($parameter instanceof Filter) {
                $resulting['bool']['filter'] = $parameter->toOpenSearchQuery();
            }
        }

        // Without a should clause, minimum_should_match would make the query match nothing, so it is left out.
        if(
            isset($this->parameters['minimum_should_match'])
            && isset($resulting['bool']['should'])
        ) {
            $resulting['bool']['minimum_should_match'] = $this->parameters['minimum_should_match'];
        }

        if(
            isset($this->parameters['boost'])
        ) {
            $resulting['bool']['boost'] = $this->parameters['boost'];
        }

        // An empty bool has to be sent as a JSON object; an empty array serialises to [], which OpenSearch rejects.
        if (!count($resulting['bool'])) {
            $resulting['bool'] = new \stdClass();
        }

        return $resulting;
    }
}
