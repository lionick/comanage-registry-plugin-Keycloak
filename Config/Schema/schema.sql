create table cm_co_keycloak_provisioner_targets
(
    id                               serial                      not null
        constraint cm_co_keycloak_provisioner_targets_pkey
            primary key,
    co_provisioning_target_id        integer                     not null
        constraint cm_co_keycloak_provisioner_targets_co_provisioning_target_id_fk
            references cm_co_provisioning_targets,
    api_base_url                     varchar(512)  default NULL::character varying,
    api_realm                        varchar(512)  default NULL::character varying,
    api_client_id                    varchar(128)  default NULL::character varying,
    api_client_secret                varchar(256)  default NULL::character varying,
    vo_roles                         varchar(256)  default NULL::character varying,
    merge_entitlements               boolean,
    urn_namespace                    varchar(128)  default NULL::character varying,
    urn_authority                    varchar(128)  default NULL::character varying,
    urn_legacy                       varchar(128)  default NULL::character varying,
    enable_vo_whitelist              boolean,
    vo_whitelist                     text,
    vo_group_prefix                  varchar(4000) default NULL::character varying,
    entitlement_format               text,
    identifier_type                  varchar(128)  default NULL::character varying,
    created                          timestamp,
    modified                         timestamp,
    deleted                          boolean       default false not null,
    entitlement_format_include_vowht boolean,
    rciam_external_entitlements      boolean,
);

alter table cm_co_keycloak_provisioner_targets
    owner to cmregistryadmin;
