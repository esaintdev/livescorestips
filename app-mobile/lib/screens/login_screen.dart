import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../core/api_client.dart';
import '../providers/auth_provider.dart';
import '../widgets/auth_widgets.dart';
import 'register_screen.dart';

class LoginScreen extends StatefulWidget {
  const LoginScreen({super.key});

  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  final _formKey = GlobalKey<FormState>();
  final _email = TextEditingController();
  final _password = TextEditingController();
  bool _remember = false;
  bool _busy = false;

  @override
  void dispose() {
    _email.dispose();
    _password.dispose();
    super.dispose();
  }

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) return;
    setState(() => _busy = true);

    try {
      await context
          .read<AuthProvider>()
          .login(_email.text.trim(), _password.text);
      if (!mounted) return;
      Navigator.of(context).pop();
    } on ApiException catch (e) {
      _toast(e.message);
    } catch (_) {
      _toast('Could not sign in. Please try again.');
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
      title: 'Sign In',
      children: [
        const SizedBox(height: 8),
        const AuthHeading(
          headline: 'Welcome back',
          supporting: 'Sign in to access your account',
        ),
        const SizedBox(height: 30),
        Form(
          key: _formKey,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
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
                label: 'Password',
                icon: Icons.lock_outline,
                controller: _password,
                hint: 'Enter your password',
                isPassword: true,
                textInputAction: TextInputAction.done,
                autofillHints: const [AutofillHints.password],
                onSubmitted: (_) => _submit(),
                validator: (v) =>
                    (v == null || v.isEmpty) ? 'Enter your password' : null,
              ),
              const SizedBox(height: 14),
              AuthCheckbox(
                value: _remember,
                onChanged: (v) => setState(() => _remember = v),
                label: 'Remember me',
              ),
              const SizedBox(height: 22),
              AuthPrimaryButton(
                label: 'Sign In',
                onPressed: _submit,
                busy: _busy,
              ),
              const SizedBox(height: 16),
              AuthSecondaryLink(
                prefix: "Don't have an account? ",
                action: 'Create one',
                onTap: () => Navigator.of(context).pushReplacement(
                  MaterialPageRoute(builder: (_) => const RegisterScreen()),
                ),
              ),
            ],
          ),
        ),
      ],
    );
  }
}
