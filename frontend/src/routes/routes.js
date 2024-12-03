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
    subtitle: 'Vista de rol',
    section: '/roles/vista',
  },
  '/gestion/crud': {
    title: TITLES.GESTION_DE_HERRAMIENTA.title,
    subtitle: 'CRUD de gestión',
    section: '/gestion/crud',
  },
  '/gestion/permisos': {
    title: TITLES.GESTION_DE_HERRAMIENTA.title,
    subtitle: 'Gestión de permisos',
    section: '/gestion/permisos',
  },
  '/gestion/contraseñas': {
    title: TITLES.GESTION_DE_HERRAMIENTA.title,
    subtitle: 'Gestión de contraseñas',
    section: '/gestion/contraseñas',
  },
  '/gestion/grupos': {
    title: TITLES.GESTION_DE_HERRAMIENTA.title,
    subtitle: 'Gestión de grupos',
    section: '/gestion/grupos',
  },
  '/familias/crud': {
    title: TITLES.GESTION_DE_FAMILIAS.title,
    subtitle: 'CRUD de familias',
    section: '/familias/crud',
  },
  '/familias/insercion/elementos': {
    title: TITLES.GESTION_DE_FAMILIAS.title,
    subtitle: 'Inserción de elementos',
    section: '/familias/insercion/elementos',
  },
  '/familias/insercion/causas': {
    title: TITLES.GESTION_DE_FAMILIAS.title,
    subtitle: 'Inserción de causas',
    section: '/familias/insercion/causas',
  },
  '/unidades/crear': {
    title: TITLES.GESTION_DE_UNIDADES.title,
    subtitle: 'Crear unidad',
    section: '/unidades/crear',
  },
};
