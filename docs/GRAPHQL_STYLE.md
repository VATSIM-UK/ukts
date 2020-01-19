# GraphQL Project Style Guide
For this project, the [nuwave/lighthouse](https://github.com/nuwave/lighthouse) GraphQL framework is used. 
Full documentation on the framework can be found at the above link. 
This document serves to inform on practises used within this project.

## Mutations
The following guidelines should be applied to the GraphQL Mutation type.
### Input Objects
When input is required for mutation type queries in the GraphQL Schema, an input type should be used to encapsulate the parameters.

Example:
```graphql
input SpecialEndorsementInput {
    name: String! @rules(apply: ["min:3"])
}

createSpecialEndorsement(input: SpecialEndorsementInput! @spread): SpecialEndorsement! @create

```

### Custom Resolvers
To avoid unnecessary complexity, the built-in directives should be used within the GraphQL schema.
Despite this, custom resolvers are often necessary to apply business logic of varying complexity.

### Validation
Relevant validation rules can be applied to the input object, inline with Laravel standard validation rules.
A full documentation of these rules are found in the Laravel documentation. 
This is possible via directives provided as part of the lighthouse-php package referenced at the start of this document.

### Existing Models
When the mutation modifies an existing model, the identifier of this model will sit outside of the input object as a standalone field.
An example of this can be found below:
```graphql
updateSpecialEndorsement(id: ID!, input: SpecialEndorsementInput! @spread): SpecialEndorsement @update
```
