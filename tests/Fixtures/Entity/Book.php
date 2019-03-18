<?php

declare(strict_types=1);

namespace ApiPlatform\ConfigurationConverter\Test\Fixtures\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiSubresource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Core\Serializer\Filter\GroupFilter;
use ApiPlatform\Core\Serializer\Filter\PropertyFilter;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ApiResource(
 *     shortName="customShortname",
 *     description="my description",
 *     iri="http://schema.org/Book",
 *     normalizationContext={"groups"={"read"}},
 *     denormalizationContext={"groups"={"write"}},
 *     itemOperations={
 *         "get"={"method"="GET", "path"="/grimoire/{id}", "requirements"={"id"="\d+"}, "defaults"={"color"="brown"}, "options"={"my_option"="my_option_value"}, "schemes"={"https"}, "host"="{subdomain}.api-platform.com"},
 *         "put"={"method"="PUT", "path"="/grimoire/{id}/update", "hydra_context"={"foo"="bar"}},
 *         "post_publication"={"method"="POST", "path"="/grimoire", "deprecation_reason"="Retrieve a Book instead", "sunset"="01/01/2020"}
 *     },
 *     collectionOperations={
 *         "get",
 *         "custom"={"method"="GET", "path"="/MyRoute"},
 *         "post"
 *     },
 *     graphql={
 *         "query"={"normalization_context"={"groups"={"query"}}},
 *         "create"={
 *             "normalization_context"={"groups"={"query"}},
 *             "denormalization_context"={"groups"={"mutation"}}
 *         },
 *         "delete"
 *     },
 *     deprecationReason="Create a Book instead"
 * )
 * @ApiFilter(GroupFilter::class, arguments={"parameterName"="groups", "overrideDefaultGroups"=false, "whitelist"={"allowed_group"}})
 * @ApiFilter(PropertyFilter::class, arguments={"parameterName"="propertyfilterparametername", "overrideDefaultProperties"=false, "whitelist"={"name", "author"}})
 * @ApiFilter(SearchFilter::class, properties={"id"="exact", "price"="exact", "description"="partial"})
 * @ApiFilter(OrderFilter::class, properties={"validFrom"={ "nulls_comparison"=OrderFilter::NULLS_SMALLEST, "default_direction"="DESC" }})
 */
class Book
{
    /**
     * @var string identifiant unique
     * @ApiProperty(
     *     readable=true,
     *     writable=true,
     *     readableLink=true,
     *     writableLink=true,
     *     required=true,
     *     iri="http://schema.org/id",
     *     identifier=true
     * )
     */
    public $id;
    /**
     * @Groups({"read", "write", "query"})
     */
    public $name;

    /**
     * @Groups({"read", "mutation"})
     *
     * @var Dummy
     * @ApiSubresource(maxDepth=1)
     */
    public $author;

    /**
     * @ApiProperty(deprecationReason="Use the author property instead")
     */
    public $scribus;
}
