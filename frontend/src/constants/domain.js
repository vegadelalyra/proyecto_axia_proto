const ROLES = {
  ADMINISTRADOR: 'Administrador',
  REPORTER: 'Reporter',
  CURATOR: 'Curator',
  REPARADOR: 'Reparador',
  DISEÑADOR: 'Diseñador',
  CLIENTE_FINAL: 'Cliente Final',
  CALIDAD: 'Calidad',
  CONTROL: 'Control',
  PLANIFICADOR: 'Planificador',
  MANAGER: 'Manager',
  FABRICANTE: 'Fabricante',
  SUPPORT: 'Support',
};

export const ROLES_MAPPING = {
  SA: ROLES.ADMINISTRADOR,
  REP: ROLES.REPORTER,
  CURA: ROLES.CURATOR,
  RP: ROLES.REPARADOR,
  RO: ROLES.DISEÑADOR,
  LO: ROLES.CLIENTE_FINAL,
  QA: ROLES.CALIDAD,
  CON: ROLES.CONTROL,
  DES: ROLES.PLANIFICADOR,
  MAN: ROLES.MANAGER,
  MANU: ROLES.FABRICANTE,
  SUP: ROLES.SUPPORT,
};

export const ROLES_CODES_MAPPING = {
  1: ROLES.SA, // Administrador
  2: ROLES.REP, // Reporter
  3: ROLES.CURA, // Curator
  4: ROLES.RP, // Reparador
  5: ROLES.RO, // Diseñador
  6: ROLES.LO, // Cliente Final
  7: ROLES.QA, // Calidad
  9: ROLES.CON, // Control
  10: ROLES.DES, // Planificador
  11: ROLES.MAN, // Manager
  12: ROLES.MANU, // Fabricante
  13: ROLES.SUP, // Support
};
