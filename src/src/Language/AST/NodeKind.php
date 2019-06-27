<?php

namespace GraphQL\Language\AST;

class NodeKind
{
    // constants from language/kinds.js:

    const NAME = 'Name';

    // Document

    const DOCUMENT = 'Document';
    const OPERATION_DEFINITION = 'OperationDefinition';
    const VARIABLE_DEFINITION = 'VariableDefinition';
    const VARIABLE = 'Variable';
    const SELECTION_SET = 'SelectionSet';
    const FIELD = 'Field';
    const ARGUMENT = 'Argument';

    // Fragments

    const FRAGMENT_SPREAD = 'FragmentSpread';
    const INLINE_FRAGMENT = 'InlineFragment';
    const FRAGMENT_DEFINITION = 'FragmentDefinition';

    // Values

    const INT = 'IntValue';
    const FLOAT = 'FloatValue';
    const STRING = 'StringValue';
    const BOOLEAN = 'BooleanValue';
    const ENUM = 'EnumValue';
    const NULL = 'NullValue';
    const LST = 'ListValue';
    const OBJECT = 'ObjectValue';
    const OBJECT_FIELD = 'ObjectField';

    // Directives

    const DIRECTIVE = 'Directive';

    // Types

    const NAMED_TYPE = 'NamedType';
    const LIST_TYPE = 'ListType';
    const NON_NULL_TYPE = 'NonNullType';

    // Type System Definitions

    const SCHEMA_DEFINITION = 'SchemaDefinition';
    const OPERATION_TYPE_DEFINITION = 'OperationTypeDefinition';

    // Type Definitions

    const SCALAR_TYPE_DEFINITION = 'ScalarTypeDefinition';
    const OBJECT_TYPE_DEFINITION = 'ObjectTypeDefinition';
    const FIELD_DEFINITION = 'FieldDefinition';
    const INPUT_VALUE_DEFINITION = 'InputValueDefinition';
    const INTERFACE_TYPE_DEFINITION = 'InterfaceTypeDefinition';
    const UNION_TYPE_DEFINITION = 'UnionTypeDefinition';
    const ENUM_TYPE_DEFINITION = 'EnumTypeDefinition';
    const ENUM_VALUE_DEFINITION = 'EnumValueDefinition';
    const INPUT_OBJECT_TYPE_DEFINITION = 'InputObjectTypeDefinition';

    // Type Extensions

    const SCALAR_TYPE_EXTENSION = 'ScalarTypeExtension';
    const OBJECT_TYPE_EXTENSION = 'ObjectTypeExtension';
    const INTERFACE_TYPE_EXTENSION = 'InterfaceTypeExtension';
    const UNION_TYPE_EXTENSION = 'UnionTypeExtension';
    const ENUM_TYPE_EXTENSION = 'EnumTypeExtension';
    const INPUT_OBJECT_TYPE_EXTENSION = 'InputObjectTypeExtension';

    // Directive Definitions

    const DIRECTIVE_DEFINITION = 'DirectiveDefinition';

    /**
     * @todo conver to const array when moving to PHP5.6
     * @var array
     */
    public static $classMap = [
        NodeKind::NAME=> '\GraphQL\Language\AST\NameNode',

        // Document
        NodeKind::DOCUMENT=> '\GraphQL\Language\AST\DocumentNode',
        NodeKind::OPERATION_DEFINITION=> '\GraphQL\Language\AST\OperationDefinitionNode',
        NodeKind::VARIABLE_DEFINITION=> '\GraphQL\Language\AST\VariableDefinitionNode',
        NodeKind::VARIABLE=> '\GraphQL\Language\AST\VariableNode',
        NodeKind::SELECTION_SET=> '\GraphQL\Language\AST\SelectionSetNode',
        NodeKind::FIELD=> '\GraphQL\Language\AST\FieldNode',
        NodeKind::ARGUMENT=> '\GraphQL\Language\AST\ArgumentNode',

        // Fragments
        NodeKind::FRAGMENT_SPREAD=> '\GraphQL\Language\AST\FragmentSpreadNode',
        NodeKind::INLINE_FRAGMENT=> '\GraphQL\Language\AST\InlineFragmentNode',
        NodeKind::FRAGMENT_DEFINITION=> '\GraphQL\Language\AST\FragmentDefinitionNode',

        // Values
        NodeKind::INT=> '\GraphQL\Language\AST\IntValueNode',
        NodeKind::FLOAT=> '\GraphQL\Language\AST\FloatValueNode',
        NodeKind::STRING=> '\GraphQL\Language\AST\StringValueNode',
        NodeKind::BOOLEAN=> '\GraphQL\Language\AST\BooleanValueNode',
        NodeKind::ENUM=> '\GraphQL\Language\AST\EnumValueNode',
        NodeKind::NULL=> '\GraphQL\Language\AST\NullValueNode',
        NodeKind::LST=> '\GraphQL\Language\AST\ListValueNode',
        NodeKind::OBJECT=> '\GraphQL\Language\AST\ObjectValueNode',
        NodeKind::OBJECT_FIELD=> '\GraphQL\Language\AST\ObjectFieldNode',

        // Directives
        NodeKind::DIRECTIVE=> '\GraphQL\Language\AST\DirectiveNode',

        // Types
        NodeKind::NAMED_TYPE=> '\GraphQL\Language\AST\NamedTypeNode',
        NodeKind::LIST_TYPE=> '\GraphQL\Language\AST\ListTypeNode',
        NodeKind::NON_NULL_TYPE=> '\GraphQL\Language\AST\NonNullTypeNode',

        // Type System Definitions
        NodeKind::SCHEMA_DEFINITION=> '\GraphQL\Language\AST\SchemaDefinitionNode',
        NodeKind::OPERATION_TYPE_DEFINITION=> '\GraphQL\Language\AST\OperationTypeDefinitionNode',

        // Type Definitions
        NodeKind::SCALAR_TYPE_DEFINITION=> '\GraphQL\Language\AST\ScalarTypeDefinitionNode',
        NodeKind::OBJECT_TYPE_DEFINITION=> '\GraphQL\Language\AST\ObjectTypeDefinitionNode',
        NodeKind::FIELD_DEFINITION=> '\GraphQL\Language\AST\FieldDefinitionNode',
        NodeKind::INPUT_VALUE_DEFINITION=> '\GraphQL\Language\AST\InputValueDefinitionNode',
        NodeKind::INTERFACE_TYPE_DEFINITION=> '\GraphQL\Language\AST\InterfaceTypeDefinitionNode',
        NodeKind::UNION_TYPE_DEFINITION=> '\GraphQL\Language\AST\UnionTypeDefinitionNode',
        NodeKind::ENUM_TYPE_DEFINITION=> '\GraphQL\Language\AST\EnumTypeDefinitionNode',
        NodeKind::ENUM_VALUE_DEFINITION=> '\GraphQL\Language\AST\EnumValueDefinitionNode',
        NodeKind::INPUT_OBJECT_TYPE_DEFINITION => '\GraphQL\Language\AST\InputObjectTypeDefinitionNode',

        // Type Extensions
        NodeKind::SCALAR_TYPE_EXTENSION=> '\GraphQL\Language\AST\ScalarTypeExtensionNode',
        NodeKind::OBJECT_TYPE_EXTENSION=> '\GraphQL\Language\AST\ObjectTypeExtensionNode',
        NodeKind::INTERFACE_TYPE_EXTENSION=> '\GraphQL\Language\AST\InterfaceTypeExtensionNode',
        NodeKind::UNION_TYPE_EXTENSION=> '\GraphQL\Language\AST\UnionTypeExtensionNode',
        NodeKind::ENUM_TYPE_EXTENSION=> '\GraphQL\Language\AST\EnumTypeExtensionNode',
        NodeKind::INPUT_OBJECT_TYPE_EXTENSION=> '\GraphQL\Language\AST\InputObjectTypeExtensionNode',

        // Directive Definitions
        NodeKind::DIRECTIVE_DEFINITION=> '\GraphQL\Language\AST\DirectiveDefinitionNode'
    ];
}
