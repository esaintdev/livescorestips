import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../core/api_client.dart';
import '../providers/auth_provider.dart';
import '../widgets/auth_widgets.dart';
import 'login_screen.dart';

class RegisterScreen extends StatefulWidget {
  const RegisterScreen({super.key});

  @override
  State<RegisterScreen> createState() => _RegisterScreenState();
}

class _RegisterScreenState extends State<RegisterScreen> {
  final _formKey = GlobalKey<FormState>();
  final _name = TextEditingController();
  final _email = TextEditingController();
  final _phone = TextEditingController();
  final _password = TextEditingController();
  final _confirm = TextEditingController();
  bool _busy = false;

  @override
  void dispose() {
    _name.dispose();
    _email.dispose();
    _phone.dispose();
    _password.dispose();
    _confirm.dispose();
    super.dispose();
  }

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) return;
    setState(() => _busy = true);

    try {
      await context.read<AuthProvider>().register(
            name: _name.text.trim(),
            email: _email.text.trim(),
            password: _password.text,
            phone: _phone.text.trim(),
          );
      if (!mounted) return;
      Navigator.of(context).pop();
    } on ApiException catch (e) {
      _toast(e.message);
    } catch (_) {
      _toast('Could not create account. Please try again.');
    } finally {
      if (mounted) setState(() => _busy = false);
    }
  }

  void _toast(String msg) {
    if (!mounted) return;
    ScaffoldMessenger.of(context)
        .showSnackBar(SnackBar(content: Text(msg)));
  }

  @override
  Widget build(BuildContext context) {
    return AuthScaffold(
      title: 'Create Account',
      children: [
        const SizedBox(height: 8),
        const AuthHeading(
          headline: 'Create your account',
          supporting: 'Join our community to access exclusive predictions',
        ),
        const SizedBox(height: 30),
        Form(
          key: _formKey,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              AuthField(
                label: 'Full Name',
                icon: Icons.person_outline,
                controller: _name,
                hint: 'Your full name',
                textCapitalization: TextCapitalization.words,
                autofillHints: const [AutofillHints.name],
                validator: (v) =>
                    (v == null || v.isEmpty) ? 'Enter your full name' : null,
              ),
              const SizedBox(height: 16),
              AuthField(
                label: 'Email',
                icon: Icons.email_outlined,
                controller: _email,
                hint: 'you@example.com',
                keyboardType: TextInputType.emailAddress,
                autofillHints: const [AutofillHints.email],
                validator: (v) => (v == null || !v.contains('@'))
                    ? 'Enter a valid email'
                    : null,
              ),
              const SizedBox(height: 16),
              AuthField(
                label: 'Phone (WhatsApp)',
                icon: Icons.phone_outlined,
                controller: _phone,
                hint: '+234...',
                keyboardType: TextInputType.phone,
                autofillHints: const [AutofillHints.telephoneNumber],
              ),
              const SizedBox(height: 16),
              AuthField(
                label: 'Password',
                icon: Icons.lock_outline,
                controller: _password,
                hint: 'Create a password',
                isPassword: true,
                validator: (v) => (v == null || v.length < 8)
                    ? 'Password must be at least 8 characters'
                    : null,
              ),
              const SizedBox(height: 16),
              AuthField(
                label: 'Confirm Password',
                icon: Icons.lock_outline,
                controller: _confirm,
                hint: 'Confirm your password',
                isPassword: true,
                textInputAction: TextInputAction.done,
                onSubmitted: (_) => _submit(),
                validator: (v) =>
                    (v != _password.text) ? 'Passwords do not match' : null,
              ),
              const SizedBox(height: 26),
              AuthPrimaryButton(
                label: 'Create Account',
                onPressed: _submit,
                busy: _busy,
              ),
              const SizedBox(height: 16),
              AuthSecondaryLink(
                prefix: 'Already have an account? ',
                action: 'Sign in',
                onTap: () => Navigator.of(context).pushReplacement(
                  MaterialPageRoute(builder: (_) => const LoginScreen()),
                ),
              ),
            ],
          ),
        ),
      ],
    );
  }
}
