export interface PersonalData {
    firstName: string
    lastName: string
    title: string
    email: string
    phone: string
    location: string
    country: string
    website: string
    photo: string
    // Detalhes adicionais (expansível)
    dateOfBirth: string
    nationality: string
    drivingLicense: string
    linkedIn: string
}

export interface WorkEntry {
    id: string
    company: string
    role: string
    location: string
    startDate: string
    endDate: string
    current: boolean
    description: string
}

export interface EducationEntry {
    id: string
    institution: string
    degree: string
    field: string
    startDate: string
    endDate: string
    current: boolean
    description: string
}

export interface LinkEntry {
    id: string
    label: string
    url: string
}

export interface LanguageEntry {
    language: string
    level: string
}

export interface CertificationEntry {
    name: string
    issuer: string
    year: string
}

export interface AdditionalData {
    languages: LanguageEntry[]
    certifications: CertificationEntry[]
    courses: string[]
    hobbies: string[]
}

export interface ResumeData {
    personalData: PersonalData
    summary: string
    workHistory: WorkEntry[]
    education: EducationEntry[]
    skills: SkillEntry[]
    links: LinkEntry[]
    additional: AdditionalData
}

export interface SkillEntry {
    id: string
    name: string
    level: number // 0 = sem nível, 1-5 = Iniciante → Especialista
}

export interface ResumeCustomization {
    color: string
    font: string
    fontSize: 'sm' | 'md' | 'lg'
    spacing: 'compact' | 'normal' | 'relaxed'
    layout: 'A4' | 'letter'
    showSkillLevels: boolean
}

export interface Resume {
    id: number
    title: string
    template_id: number
    data: ResumeData
    customization: ResumeCustomization
    current_version: number
    is_downloaded: boolean
    created_at: string
    updated_at: string
}

export const defaultResumeData = (): ResumeData => ({
    personalData: {
        firstName: '',
        lastName: '',
        title: '',
        email: '',
        phone: '',
        location: '',
        country: 'Brasil',
        website: '',
        photo: '',
        dateOfBirth: '',
        nationality: '',
        drivingLicense: '',
        linkedIn: '',
    },
    summary: '',
    workHistory: [],
    education: [],
    skills: [],
    links: [],
    additional: {
        languages: [],
        certifications: [],
        courses: [],
        hobbies: [],
    },
})

export const defaultCustomization = (): ResumeCustomization => ({
    color: '#2563eb',
    font: 'Inter',
    fontSize: 'md',
    spacing: 'normal',
    layout: 'A4',
    showSkillLevels: true,
})
