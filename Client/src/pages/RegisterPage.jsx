import { useState } from 'react'
import { Link, useNavigate } from 'react-router-dom'
import { Card, Form, Button, Alert, Container } from 'react-bootstrap'
import { useForm } from 'react-hook-form'
import { zodResolver } from '@hookform/resolvers/zod'
import { z } from 'zod'
import { useAuth } from '../auth/AuthContext'

// Mirror the server-side rules so the user gets instant feedback.
// Server is still authoritative — if it disagrees we show whatever
// it sends back in `serverErrors`.
const schema = z.object({
  name: z.string().min(2, 'Name must be at least 2 characters'),
  email: z.string().email('Enter a valid email'),
  password: z.string()
    .min(8, 'At least 8 characters')
    .regex(/[A-Za-z]/, 'Must contain a letter')
    .regex(/[0-9]/, 'Must contain a number'),
  password_confirmation: z.string(),
}).refine((data) => data.password === data.password_confirmation, {
  message: 'Passwords do not match',
  path: ['password_confirmation'],
})

export default function RegisterPage() {
  const { register: signUp } = useAuth()
  const navigate = useNavigate()
  const [serverErrors, setServerErrors] = useState(null)

  const { register, handleSubmit, formState: { errors, isSubmitting } } = useForm({
    resolver: zodResolver(schema),
  })

  async function onSubmit(values) {
    setServerErrors(null)
    try {
      await signUp(values)
      navigate('/')
    } catch (err) {
      const data = err.response?.data
      if (data?.errors) {
        // Flatten Laravel's { field: [msg1, msg2] } into a list.
        setServerErrors(Object.values(data.errors).flat())
      } else {
        setServerErrors([data?.message ?? 'Registration failed.'])
      }
    }
  }

  return (
    <Container style={{ maxWidth: 460 }} className="mt-5">
      <Card>
        <Card.Body>
          <h1 className="h3 mb-3 text-center">Create your account</h1>

          {serverErrors && (
            <Alert variant="danger">
              <ul className="mb-0">
                {serverErrors.map((m, i) => <li key={i}>{m}</li>)}
              </ul>
            </Alert>
          )}

          <Form onSubmit={handleSubmit(onSubmit)} noValidate>
            <Form.Group className="mb-3">
              <Form.Label>Name</Form.Label>
              <Form.Control isInvalid={!!errors.name} {...register('name')} />
              <Form.Control.Feedback type="invalid">{errors.name?.message}</Form.Control.Feedback>
            </Form.Group>

            <Form.Group className="mb-3">
              <Form.Label>Email</Form.Label>
              <Form.Control type="email" isInvalid={!!errors.email} {...register('email')} />
              <Form.Control.Feedback type="invalid">{errors.email?.message}</Form.Control.Feedback>
            </Form.Group>

            <Form.Group className="mb-3">
              <Form.Label>Password</Form.Label>
              <Form.Control type="password" isInvalid={!!errors.password} {...register('password')} />
              <Form.Text>At least 8 chars, with a letter and a number.</Form.Text>
              <Form.Control.Feedback type="invalid">{errors.password?.message}</Form.Control.Feedback>
            </Form.Group>

            <Form.Group className="mb-3">
              <Form.Label>Confirm password</Form.Label>
              <Form.Control type="password" isInvalid={!!errors.password_confirmation} {...register('password_confirmation')} />
              <Form.Control.Feedback type="invalid">{errors.password_confirmation?.message}</Form.Control.Feedback>
            </Form.Group>

            <Button type="submit" className="w-100" disabled={isSubmitting}>
              {isSubmitting ? 'Creating account…' : 'Create account'}
            </Button>
          </Form>

          <p className="text-center mt-3 mb-0">
            Already have an account? <Link to="/login">Sign in</Link>
          </p>
        </Card.Body>
      </Card>
    </Container>
  )
}
