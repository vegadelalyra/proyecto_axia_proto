export const TITLES = {
  ROLES: {
    title: 'Roles',
    path: 'roles',
    subtitles: [{ name: 'Vista de rol', path: '/roles/vista' }],
  },
  GESTION_DE_HERRAMIENTA: {
    title: 'Gestión de Herramienta',
    path: 'gestion',
    subtitles: [
      { name: 'CRUD', path: '/gestion/crud' },
      { name: 'Permisos', path: '/gestion/permisos' },
      { name: 'Contraseñas', path: '/gestion/contraseñas' },
      { name: 'Grupos', path: '/gestion/grupos' },
    ],
  },
  GESTION_DE_FAMILIAS: {
    title: 'Gestión de Familias',
    path: 'familias',
    subtitles: [
      { name: 'CRUD familias', path: '/familias/crud' },
      { name: 'Inserción elementos', path: '/familias/insercion/elementos' },
      { name: 'Inserción causas', path: '/familias/insercion/causas' },
    ],
  },
  GESTION_DE_UNIDADES: {
    title: 'Gestión de Unidades',
    path: 'unidades',
    subtitles: [{ name: 'Crear unidad', path: '/unidades/crear' }],
  },
};

export const ROUTES = {
  '/roles/vista': {
    title: TITLES.ROLES.title,
    subtitle: TITLES.ROLES.subtitles[0].name,
    section: TITLES.ROLES.subtitles[0].path,
  },
  '/gestion/crud': {
    title: TITLES.GESTION_DE_HERRAMIENTA.title,
    subtitle: TITLES.GESTION_DE_HERRAMIENTA.subtitles[0].name,
    section: TITLES.GESTION_DE_HERRAMIENTA.subtitles[0].path,
  },
  '/gestion/permisos': {
    title: TITLES.GESTION_DE_HERRAMIENTA.title,
    subtitle: TITLES.GESTION_DE_HERRAMIENTA.subtitles[1].name,
    section: TITLES.GESTION_DE_HERRAMIENTA.subtitles[1].path,
  },
  '/gestion/contraseñas': {
    title: TITLES.GESTION_DE_HERRAMIENTA.title,
    subtitle: TITLES.GESTION_DE_HERRAMIENTA.subtitles[2].name,
    section: TITLES.GESTION_DE_HERRAMIENTA.subtitles[2].path,
  },
  '/gestion/grupos': {
    title: TITLES.GESTION_DE_HERRAMIENTA.title,
    subtitle: TITLES.GESTION_DE_HERRAMIENTA.subtitles[3].name,
    section: TITLES.GESTION_DE_HERRAMIENTA.subtitles[3].path,
  },
  '/familias/crud': {
    title: TITLES.GESTION_DE_FAMILIAS.title,
    subtitle: TITLES.GESTION_DE_FAMILIAS.subtitles[0].name,
    section: TITLES.GESTION_DE_FAMILIAS.subtitles[0].path,
  },
  '/familias/insercion/elementos': {
    title: TITLES.GESTION_DE_FAMILIAS.title,
    subtitle: TITLES.GESTION_DE_FAMILIAS.subtitles[1].name,
    section: TITLES.GESTION_DE_FAMILIAS.subtitles[1].path,
  },
  '/familias/insercion/causas': {
    title: TITLES.GESTION_DE_FAMILIAS.title,
    subtitle: TITLES.GESTION_DE_FAMILIAS.subtitles[2].name,
    section: TITLES.GESTION_DE_FAMILIAS.subtitles[2].path,
  },
  '/unidades/crear': {
    title: TITLES.GESTION_DE_UNIDADES.title,
    subtitle: TITLES.GESTION_DE_UNIDADES.subtitles[0].name,
    section: TITLES.GESTION_DE_UNIDADES.subtitles[0].path,
  },
};
