export interface UpdateProfileData {
  full_name?: string
  phone?: string
  language?: string
  avatar?: File
}

export interface ChangePasswordData {
  current_password: string
  new_password: string
  new_password_confirmation: string
}
