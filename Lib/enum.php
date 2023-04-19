<?php


class KeycloakProvisionerDateTruncEnum
{
    const daily     = 'day';
    const weekly    = 'week';
    const monthly   = 'month';
    const yearly    = 'year';

    const type      = array(
        'daily'     => 'day',
        'weekly'    => 'week',
        'monthly'   => 'month',
        'yearly'    => 'year',
    );
}

class KeycloakProvisionerDateEnum
{
    const daily     = 'daily';
    const weekly    = 'weekly';
    const monthly   = 'monthly';
    const yearly    = 'yearly';

    const type      = array(
        'weekly'    => 'weekly',
        'monthly'   => 'monthly',
        'yearly'    => 'yearly',
    );
}

class KeycloakProvisionerIdentifierEnum
{
  const Badge              = 'badge';
  const Enterprise         = 'enterprise';
  const ePPN               = 'eppn';
  const ePTID              = 'eptid';
  const ePUID              = 'epuid';
  const Mail               = 'mail';
  const National           = 'national';
  const Network            = 'network';
  const OpenID             = 'openid';
  const ORCID              = 'orcid';
  const ProvisioningTarget = 'provisioningtarget';
  const Reference          = 'reference';
  const SORID              = 'sorid';
  const UID                = 'uid';

  const type = array(
    KeycloakProvisionerIdentifierEnum::Badge => 'badge',
    KeycloakProvisionerIdentifierEnum::Enterprise => 'enterprise',
    KeycloakProvisionerIdentifierEnum::ePPN => 'eppn',
    KeycloakProvisionerIdentifierEnum::ePTID => 'eptid',
    KeycloakProvisionerIdentifierEnum::ePUID => 'epuid',
    KeycloakProvisionerIdentifierEnum::Mail => 'mail',
    KeycloakProvisionerIdentifierEnum::National => 'national',
    KeycloakProvisionerIdentifierEnum::Network => 'network',
    KeycloakProvisionerIdentifierEnum::OpenID => 'openid',
    KeycloakProvisionerIdentifierEnum::ORCID => ' orcid',
    KeycloakProvisionerIdentifierEnum::ProvisioningTarget => 'provisioningtarget',
    KeycloakProvisionerIdentifierEnum::Reference => 'reference',
    KeycloakProvisionerIdentifierEnum::SORID => 'sorid',
    KeycloakProvisionerIdentifierEnum::UID => 'uid'
  );
}

class KeycloakProvisionerRciamSyncVomsCfg {
  const VoBlackList = array(
    'vo.elixir-europe.org'
  );
  const UserIdAttribute   = 'distinguishedName';
  const TableName         = 'cm_voms_members';
}
