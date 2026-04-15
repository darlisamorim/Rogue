export interface TemplateConfig {
    colors: string[]
    fonts: string[]
}

export interface Template {
    id: number
    name: string
    slug: string
    thumbnail_url: string | null
    component_name: string
    config: TemplateConfig
    is_active: boolean
    sort_order: number
}
